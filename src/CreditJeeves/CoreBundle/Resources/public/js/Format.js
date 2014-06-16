Format = {
    money: function(number, currency) {
        if (isNaN(number) || !number) {
            return '';
        }
        if (!currency) {
            currency = 'USD';
        }
        return Format.currencySymbol(currency) + Format.number(number);
    },
    currencySymbol: function(currency) {
        switch(currency) {
            case 'USD':
                return '$';
        }
        return '';
    },
    number: function(number, decimals) {
        if (!decimals) {
            decimals = 2;
        }
        return parseFloat(number).toFixed(decimals);
    }
};
