let umaVar = 8;

$(function () {



    general.REQUEST_API = function () {

        return new Promise((resolve, reject) => {

            $.get(`${general.BASE_URL}/shopping-cart`, function (data) {

                cartDataHandler(data);
                resolve(data);
            }).fail(function (e) {
                reject(e);
            });
        });
    }

    function cartDataHandler(data){

        // carregando os elementos do DOM.
        let ulOrder = $("#areaOrder");
        let orderItemsNum = $("#orderItemsNum");

        // atualiza a quantidade de itens no carrinho.
        orderItemsNum.text(data.items_num)

        let items = data.items;

        let html = "";

        // cria um elemento HTML 'li' para cada item do carrinho.
        items.forEach(function (item) {

            html += createItemHTML(item);
        });

        // cria um elemento HTML 'li' para o item subtotal.
        html += createSubtotalHtml(data.value.subtotal);

        // cria um elemento HTML 'li' para o desconto.
        html += createOffValueHTML(data.value.off);

        // cria um elemento HTML 'li' para o total.
        html += createTotalHtml(data.value.total);

        // adiciona o HTML criado (conjunto de li)
        // na ul da compra.
        ulOrder.append(html);
    }

    function createItemHTML(item) {
        return `
        <li class="list-group-item d-flex justify-content-between lh-condensed">
            <div>
                <h6 class="my-0">${item.title}</h6>
                    <small class="text-muted">
                        <em>
                            ${item.desc}
                        </em>
                    </small>
                </div>
            <span class="text-muted">${currencyFormat(item.price)}</span>
        </li>
        `;
    }

    function createOffValueHTML(off) {
        return `
        <li class="list-group-item d-flex justify-content-between bg-light">
            <div class="text-success">
                <h6 class="my-0">Código de promoção</h6>
                <small>${off.code}</small>
            </div>
            <span class="text-success">-${currencyFormat(off.value)}</span>
        </li>
      `;
    }

    function createSubtotalHtml(subtotal) {
        return `
        <li class="list-group-item d-flex justify-content-between lh-condensed">
            <span class="my-0">Subotal (BRL)</span>
            <strong>${currencyFormat(subtotal)}</strong>
        </li>
        `;
    }

    function createTotalHtml(total) {
        return `
        <li class="list-group-item d-flex justify-content-between">
            <span>Total (BRL)</span>
            <strong>${currencyFormat(total)}</strong>
        </li>
        `;
    }

    function currencyFormat(price) {

        let op = {
            style: 'currency',
            currency: 'BRL'
        };
        return price.toLocaleString('pt-br', op);
    }
});