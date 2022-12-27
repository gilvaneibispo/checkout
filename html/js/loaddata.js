$(function () {

    general.CURRENCY_FORMAT = function (price) {

        let op = {
            style: 'currency',
            currency: 'BRL'
        };
        return price.toLocaleString('pt-br', op);
    }

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
                <h6 class="">${item.title}</h6>
                    <small class="text-muted">
                        <em>
                            ${item.desc}
                        </em>
                    </small>
                </div>
            <span class="text-muted">${general.CURRENCY_FORMAT(item.price)}</span>
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
            <span class="text-success">-${general.CURRENCY_FORMAT(off.value)}</span>
        </li>
      `;
    }

    function createSubtotalHtml(subtotal) {
        return `
        <li class="list-group-item d-flex justify-content-between lh-condensed">
            <span class="my-0">Subotal (BRL)</span>
            <strong>${general.CURRENCY_FORMAT(subtotal)}</strong>
        </li>
        `;
    }

    function createTotalHtml(total) {
        return `
        <li class="list-group-item d-flex justify-content-between">
            <span>Total (BRL)</span>
            <strong>${general.CURRENCY_FORMAT(total)}</strong>
        </li>
        `;
    }
});