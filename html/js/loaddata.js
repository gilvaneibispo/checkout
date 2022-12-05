let umaVar = 8;

$(function () {
    let areaOrder = $("#areaOrder");
    let orderItemsNum = $("#orderItemsNum");


    general.REQUEST_API = function () {

        return new Promise((resolve, reject) => {

            $.get(`${general.BASE_URL}/shopping-cart`, function (data) {

                orderItemsNum.text(data.items_num)

                let items = data.items;

                let html = "";
                items.forEach(function (item) {
                    html += createItemHTML(item);
                });

                html += createSubtotalHtml(data.value.subtotal);
                html += createOffValueHTML(data.value.off)
                html += createTotalHtml(data.value.total);
                areaOrder.append(html);

                resolve(data);
            }).fail(function (e) {
                reject(e);
            });
        });
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