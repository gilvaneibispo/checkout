$(function () {

    const BASE_URL = "http://127.0.0.1:8000/"
    let amount = 25.90;
    let brandCard = null;
    let maxInst = 12;
    let numberInst = 1;
    let cardEncrypt;
    let publicKey;
    let lockAction = false;
    let productKey = "MEUPRODUTO01";

    $('#nav-tab a').on('click', function (e) {
        e.preventDefault()
        $(this).tab('show')
    });


    //$.get(`./pix-static.php?item_id=${productKey}&amount=${amountCar}`, function (resp){
    $.get(`${BASE_URL}`, function (resp) {
        let qrcode = JSON.parse(resp);
        $("#qrcode").attr('src', qrcode.qrcode);
        $("#textCode").text(qrcode.text_code)
        //console.log(resp);
    });


    /*
    $.get("./test.php", function (resp) {
        console.log(resp);
        publicKey = resp.public_key;
        console.log("publicKey: ", publicKey);
    });



    $(document).on('change', '#inputInst', function (e) {
        e.preventDefault();

        numberInst = $(this).val();
        console.log(numberInst);
    })

    $.get('./session-create.php', function (resp) {

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

                    /*
                    $.get("./boleto.php", function (resp) {

                        console.log(resp);
                        if (resp[0].success) {
                            $("#iframeBoleto").attr('src', resp[0].boleto_link);
                            $("#boletoNumber").text(resp[0].formatted_barcode)
                            console.log("Código do boleto: " + resp[0]);
                        }
                    });* /

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