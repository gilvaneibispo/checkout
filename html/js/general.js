let general = {
    BASE_URL: 'http://127.0.0.1:8000',
    PS_FLAG_URL: "https://stc.pagseguro.uol.com.br",
    REQUEST_API: null,
    CURRENCY_FORMAT: function (price) {

        let op = {
            style: 'currency',
            currency: 'BRL'
        };
        return price.toLocaleString('pt-br', op);
    }
}