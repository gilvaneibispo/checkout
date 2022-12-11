$(function () {

    let publicKey = null;
    let idSession = null;
    let order = null;
    let numberInst = 1;
    let requestingBrand = false;
    let lockAction = false;
    let maxInst = 12;
    let brandCard = null;

    general.REQUEST_API().then((resp) => {
        selectInstallmentsNumber();
        initPayment(resp);
    });

    function initPayment(resp) {

        order = resp;
        bootStrapTabConfig();
        loadStaticPix(resp.order_ref);
        loadDynamicPix(resp.order_ref);
        getPSPublicKey();
        keyUpInputCardHandler();

        $('.loading').css({
            display: 'none'
        });
    }


    function bootStrapTabConfig() {
        $('#nav-tab a').on('click', function (e) {
            e.preventDefault()
            $(this).tab('show')
        });
    }


    function loadStaticPix(orderRef) {

        $.get(`${general.BASE_URL}/pay/pix/static/${orderRef}`, function (resp) {

            $("#qrcode").attr('src', resp.location);
            $("#textCode").text(resp.payload);
        });
    }

    function loadDynamicPix(orderRef) {

        $.get(`${general.BASE_URL}/pay/pix/dynamic/${orderRef}`, function (resp) {

            $("#qrcodeDyn").attr('src', resp.location);
            $("#textCodeDyn").text(resp.payload);
        });
    }


    function getPSPublicKey() {

        //// MUDAR A ROTA NO SERVIDOR PARA /PAY/CARD/{TYPE}/START
        //// E A CONFIRMAÇÃO COMO /PAY/CARD/{TYPE}/CONFIRM
        $.get(`${general.BASE_URL}/pay/card/credit/start`, function (resp) {

            console.log(resp);
            publicKey = resp.public_key;
            getBan(resp.session_id);
        });
    }

    function getBan(sessionId) {
        PagSeguroDirectPayment.setSessionId(sessionId);

        console.log(order);
        PagSeguroDirectPayment.getPaymentMethods({
            amount: order.value.total,
            success: function (response) {

                // se o pagamento por cartão estiver disponível
                // carrega as bandeirinhas dos cartões disponíveis.
                flagHandler(response.paymentMethods.CREDIT_CARD.options);
                console.log(response);
            },
            error: function (e) {
                console.log(e.responseText);
            }
        });
    }

    function flagHandler(optionsCard) {


        if (optionsCard) {

            let theHtml = "";

            Object.keys(optionsCard).map(function (key) {

                theHtml += imgFlagHTMLCreate(optionsCard[key])

            });

            console.log(theHtml)

            $(".bandeira-box").html(theHtml);
        }
    }

    function imgFlagHTMLCreate(item) {

        let theHtml = "";

        if (item.status === "AVAILABLE") {

            theHtml += `<img src="${general.PS_FLAG_URL + item.images.MEDIUM.path}"`;
            theHtml += `title="Cartão ${item.name} está disponível!"`;
            theHtml += `alt="CARTÃO ${item.name}"/>`
        }

        return theHtml;
    }


    function selectInstallmentsNumber() {
        $(document).on('change', '#inputInst', function (e) {
            e.preventDefault();

            numberInst = $(this).val();
        })
    }

    function keyUpInputCardHandler() {

        $("#inputNumberCard").on('keyup', function () {

            let val = $(this).val();

            let lastChar = val.substring(val.length - 1, val.length);

            if (isNaN(lastChar)) {

                $(this).val(val.substring(0, val.length - 1))
            }

            if (val.length >= 6 && val.length <= 8) {

                if (!requestingBrand) {
                    requestingBrand = true;

                    requestBrandCard(val);
                }
            }

            if (val.length < 16 && lockAction === true) {
                lockAction = false;
            }

            if (val.length === 16 && lockAction === false) {

                lockAction = true;

                console.log("Chamando installment");
                installmentCheck();
            }

        });
    }

    function requestBrandCard(val) {

        PagSeguroDirectPayment.getBrand({
            cardBin: val,
            success: function (response) {
                //bandeira encontrada
                console.log(response);

                requestingBrand = false;
                brandCard = response.brand.name;
                $("#textBrand").text("Cartão " + response.brand.name);
                $("#inputCvv").attr('maxlength', response.brand.cvvSize).attr('minlength', response.brand.cvvSize);
            },
            error: function (response) {
                //tratamento do erro
                console.log(response);
            }
        });
    }

    function installmentCheck() {
        PagSeguroDirectPayment.getInstallments({
            amount: order.value.total,
            maxInstallmentNoInterest: maxInst,
            brand: brandCard,
            success: function (response) {
                // Retorna as opções de parcelamento disponíveis

                let inst = response.installments[brandCard];

                let theOptions = "";

                console.log(inst);
                $.each(inst, function (index, item) {


                    theOptions += createInstallmentOptionHTML(item);
                });

                $("#inputInst").html(theOptions);

                console.log(inst);
            },
            error: function (response) {
                // callback para chamadas que falharam.
            },
            complete: function (response) {
                // Callback para todas chamadas.
            }
        });
    }

    function createInstallmentOptionHTML(item) {

        let instInBRL = general.CURRENCY_FORMAT(item.installmentAmount);
        let instSum = general.CURRENCY_FORMAT(item.totalAmount);

        console.log(instInBRL + " - " + instSum);

        let textOption = `${item.quantity}x  de ${instInBRL} - `;
        textOption += (item.interestFree ? "Sem juros" : `Total ${instSum}`);

        return `<option value="${item.quantity}">${textOption}</option>`;
    }

    $("#payAction").on('click', function (e) {

        e.preventDefault();

        let nameCard = $("#inputNameCard").val();
        let numberCard = $("#inputNumberCard").val();
        let inputCardCvv = $("#inputCvv");
        let cardCvv = inputCardCvv.val();
        let expMonthCard = $("#inputExpMonth").val();
        let expYearCard = $("#inputExpYear").val();

        let encryptedCard = null;

        console.log({
            publicKey: publicKey,
            holder: nameCard,
            number: numberCard,
            expMonth: expMonthCard,
            expYear: expYearCard,
            securityCode: cardCvv,
            installment: numberInst
        })

        let card = PagSeguro.encryptCard({
            publicKey: publicKey,
            holder: nameCard,
            number: numberCard,
            expMonth: expMonthCard,
            expYear: expYearCard,
            securityCode: cardCvv
        });

        if (card.hasErrors) {

            showCardError(card.errors[0].code);
            console.log(card.errors)
        } else {
            encryptedCard = card.encryptedCard;
            //console.log(encryptedKey);
            //btnPay.removeAttr('disabled')

            $.ajax({
                url: `${general.BASE_URL}/pay/card/credit/confirm`,
                type: 'post',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    //'total_price': order.value.total * 100,
                    'order_ref': order.order_ref,
                    'installment': numberInst,
                    'encrypted_card': encryptedCard,
                }),
                success: function (data) {
                    console.log(data);

                    let buyStatus = data.success.payment_response;

                    if (buyStatus.message === "SUCESSO") {
                        alert('Pagamento aprovado - Referência: ' + buyStatus.reference);
                    } else {
                        alert('Houve um erro na tentativa de realizar o pagamento!');
                    }
                },
            }).fail(function (e) {
                console.log(e.responseText);
            });
        }


    });

    function showCardError(theError) {

        let errorMsg = "Erro no reconhecimento do cartão";

        switch (theError) {
            case "INVALID_EXPIRATION_YEAR":
            case "INVALID_EXPIRATION_MONTH":
                errorMsg = "Erro: Verifique a data de expiração do cartão!";
                break;
            case "INVALID_NUMBER":
                errorMsg = "O número do cartão não é válido!";
                break;
            case "INVALID_SECURITY_CODE":
                errorMsg = "Erro: Verifique o código de segurança do cartão!"
        }

        alert(errorMsg);
    }

    /*
    $.get(`${general.BASE_URL}/pay/card/true`, function (resp) {

        $(".loading").css({
            display: 'none',
            transition: 'all .4s'
        })
        resp = JSON.parse(resp);
        let session = resp.id;


        PagSeguroDirectPayment.setSessionId(session);

        PagSeguroDirectPayment.getPaymentMethods({
            amount: amountCar,
            success: function (response) {

                // Retorna os meios de pagamento disponíveis.
                console.log(response);





                if (response.paymentMethods.BOLETO.options.BOLETO.status === "AVAILABLE") {


                    $.get("./boleto.php", function (resp) {

                        console.log(resp);
                        if (resp[0].success) {
                            $("#iframeBoleto").attr('src', resp[0].boleto_link);
                            $("#boletoNumber").text(resp[0].formatted_barcode)
                            console.log("Código do boleto: " + resp[0]);
                        }
                    });

                    let data = {
                        "data": {
                            ref: "pack_002",
                            desc: "Uma mensagem aqui...",
                            amount: amountCar * 100
                        },
                        "holder": {
                            date_max: "2022-12-05",
                            name: "Felix Rios",
                            cpf: "03243286544",
                            email: "gilvanei.m13@gmail.com"
                        },
                        "address": {
                            city: "Feira de Santana",
                            street: "Rua Araguacema",
                            number: "64A",
                            region: {
                                name: "Bahia",
                                code: "BA"
                            },
                            locality: "Papagaio",//bairro
                            postal_code: "44061060",
                        }
                    }

                    $.ajax({
                        url: './boleto.php',
                        type: 'post',
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function (resp) {

                            dataBoleto = resp[0];
                            console.log("BOLETO: ", resp[0]);

                            if (dataBoleto.success) {
                                $("#iframeBoleto").attr('src', dataBoleto.boleto_link);
                                $("#boletoNumber").text(dataBoleto.formatted_barcode)
                                console.log("Código do boleto: " + dataBoleto.formatted_barcode);
                            } else {
                                alert("Erro interno!");
                            }
                        }
                    }).fail(function (e) {
                        console.log(e.responseText);
                    });
                }


                let allOptionsCard = response.paymentMethods.CREDIT_CARD.options;

                if (allOptionsCard) {

                    let theHtml = "";


                    $.each(allOptionsCard, function (index, item) {

                        if (item.status === "AVAILABLE") {
                            theHtml += "<img src='https://stc.pagseguro.uol.com.br";
                            theHtml += item.images.MEDIUM.path + "'";
                            theHtml += "title='Cartão " + item.name + " está disponível!'"
                            theHtml += "alt='CARTÃO " + item.name + "'/>"
                        }
                    });

                    $(".bandeira-box").html(theHtml);
                }
            },
            error: function (response) {
                // Callback para chamadas que falharam.
                console.log(response);
            }
        });
    }).fail(function (error) {
        console.error(error);
    });


    $("#payAction").on('click', function (e) {
        e.preventDefault();

        let nameCard = $("#inputNameCard").val();
        let numberCard = $("#inputNumberCard").val();
        let inputCardCvv = $("#inputCvv");
        let cardCvv = inputCardCvv.val();
        let expMonthCard = $("#inputExpMonth").val();
        let expYearCard = $("#inputExpYear").val();

        console.log({
            publicKey: publicKey,
            holder: nameCard,
            number: numberCard,
            expMonth: expMonthCard,
            expYear: expYearCard,
            securityCode: cardCvv,
            installment: numberInst
        })

        let card = PagSeguro.encryptCard({
            publicKey: publicKey,
            holder: nameCard,
            number: numberCard,
            expMonth: expMonthCard,
            expYear: expYearCard,
            securityCode: cardCvv
        });

        if (card.hasErrors) {

            let theError = card.errors[0].code;

            if (theError === "INVALID_EXPIRATION_YEAR" || theError === "INVALID_EXPIRATION_MONTH") {
                alert("Não foi possível processar o pagamento, erro na data de expiração!");
            }else

            if (theError === "INVALID_NUMBER") {
                alert("Cartão invalido");
            }else

            if (theError === "INVALID_SECURITY_CODE") {
                alert("Código de segurança errado!");
            }else{
                alert("Erro interno");
            }
            console.log(card.errors)
        } else {
            cardEncrypt = card.encryptedCard;
            //console.log(encryptedKey);
            //btnPay.removeAttr('disabled')

            $.ajax({
                url: './test.php',
                type: 'post',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    'total_price': amountCar * 100,
                    'public_key': cardEncrypt,
                    'installment': numberInst
                }),
                success: function (data) {
                    console.log(data);

                    let buyStatus = data.success.payment_response;

                    if (buyStatus.message === "SUCESSO") {
                        alert('Pagamento aprovado - Referência: ' + buyStatus.reference);
                    } else {
                        alert('Houve um erro na tentativa de realizar o pagamento!');
                    }
                },
            }).fail(function (e) {
                console.log(e.responseText);
            });
        }

        PagSeguroDirectPayment.onSenderHashReady(function (response) {

            if (response.status === 'error') {
                console.log(response.message);
                return false;
            }

            var hash = response.senderHash; //Hash estará disponível nesta variável.

            console.log(hash);
        });
    });

    function getValBRL(theVal) {


        theVal = theVal.toFixed(2).toString();
        theVal = theVal.replace('.', ',');
        theVal = `R$ ${theVal}`;
        console.log(theVal);
        return theVal;
    }

    $("#inputNumberCard").on('keyup', function () {
        let val = $(this).val();

        let lastChar = val.substring(val.length - 1, val.length);

        if (isNaN(lastChar)) {
            //alert("apenas numeros são aceitos");
            $(this).val(val.substring(0, val.length - 1))
        }

        if (val.length >= 6 && val.length <= 8) {
            PagSeguroDirectPayment.getBrand({
                cardBin: val,
                success: function (response) {
                    //bandeira encontrada
                    console.log(response);

                    brandCard = response.brand.name;
                    $("#textBrand").text("Cartão " + response.brand.name);
                    $("#inputCvv").attr('maxlength', response.brand.cvvSize).attr('minlength', response.brand.cvvSize);
                },
                error: function (response) {
                    //tratamento do erro
                    console.log(response);
                }
            });
        }


        if (val.length < 16 && lockAction === true) {
            lockAction = false;
        }
        if (val.length === 16 && lockAction === false) {

            lockAction = true;

            console.log("Chamando installment");

            PagSeguroDirectPayment.getInstallments({
                amount: amountCar,
                maxInstallmentNoInterest: maxInst,
                brand: brandCard,
                success: function (response) {
                    // Retorna as opções de parcelamento disponíveis

                    let inst = response.installments[brandCard];

                    let theOptions = "";
                    let textOption = "";
                    $.each(inst, function (index, item) {

                        let instInBRL = getValBRL(item.installmentAmount);
                        let instSum = getValBRL(item.totalAmount);
                        console.log(instInBRL + " - " + instSum);

                        textOption = item.quantity + "x  de " + instInBRL + " - (" + instSum;
                        textOption += (item.interestFree ? ' sem juros)' : ')');
                        theOptions += "<option value='" + item.quantity + "'>" + textOption + "</option>";

                    });

                    $("#inputInst").html(theOptions);

                    console.log(inst);
                },
                error: function (response) {
                    // callback para chamadas que falharam.
                },
                complete: function (response) {
                    // Callback para todas chamadas.
                }
            });
        }

        console.log(lastChar);
    });

     */
});