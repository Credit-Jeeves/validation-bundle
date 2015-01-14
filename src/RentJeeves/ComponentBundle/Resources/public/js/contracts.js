function Contracts() {
    var limit = 10;
    var current = 1;
    var self = this;
    this.aContracts = ko.observableArray([]);
    this.pages = ko.observableArray([]);
    this.total = ko.observable(0);
    this.current = ko.observable(1);
    this.sort = ko.observable('ASC');
    this.sortColumn = ko.observable("status");
    this.isSortAsc = ko.observable(true);
    this.searchText = ko.observable("");
    this.searchCollum = ko.observable("");
    this.isSearch = ko.observable(false);
    this.notHaveResult = ko.observable(false);
    this.processLoading = ko.observable(true);
    this.needRefresh = ko.observableArray([]);

    this.search = function () {
        var searchCollum = $('#searchFilter').linkselect('val');
        if (typeof searchCollum != 'string') {
            searchCollum = '';
        }
        if (searchCollum != 'status') {
            if (self.searchText().length <= 0) {
                $('#searsh-field-payments').css('border-color', 'red');
                return;
            } else {
                $('#searsh-field-payments').css('border-color', '#bdbdbd');
            }
        } else {
            var searchText = $('#searchPaymentsStatus').linkselect('val');
            if (typeof searchText != 'string') {
                searchText = '';
            }
            self.searchText(searchText);
        }
        self.isSearch(true);
        self.searchText(self.searchText());
        self.searchCollum(searchCollum);
        self.current(1);
        self.ajaxAction();
    };

    this.clearSearch = function () {
        self.searchText('');
        self.searchCollum('');
        self.current(1);
        self.ajaxAction();
        self.isSearch(false);
    };

    this.sortFunction = function (data, event) {
        field = event.target.id;

        if (field.length == 0) {
            return;
        }
        self.sortColumn(field);
        $('.sort-dn').attr('class', 'sort');
        $('.sort-up').attr('class', 'sort');
        if (self.isSortAsc() === false) {
            self.isSortAsc(true);
            $('#'.field).attr('class', 'sort-dn');
        } else {
            self.isSortAsc(false);
            $('#'.field).attr('class', 'sort-up');
        }

        self.current(1);
        self.ajaxAction();
    };

    this.ajaxAction = function () {
        $('.content-box').show();
        self.aContracts([]);
        self.notHaveResult(false);
        self.processLoading(true);
        $.ajax({
            url: Routing.generate('landlord_contracts_list'),
            type: 'POST',
            dataType: 'json',
            data: {
                'data': {
                    'page': self.current(),
                    'limit': limit,
                    'sortColumn': self.sortColumn(),
                    'isSortAsc': self.isSortAsc(),
                    'searchCollum': self.searchCollum(),
                    'searchText': self.searchText()
                }
            },
            success: function (response) {
                self.processLoading(false);
                self.needRefresh([]);
                self.aContracts([]);
                self.aContracts(response.contracts);

                self.total(response.total);
                self.pages(response.pagination);
                if (self.countContracts() <= 0) {
                    self.notHaveResult(true);
                } else {
                    self.notHaveResult(false);
                }
                if (self.sortColumn().length == 0) {
                    return;
                }
                if (self.isSortAsc()) {
                    $('#' + self.sortColumn()).attr('class', 'sort-dn');
                } else {
                    $('#' + self.sortColumn()).attr('class', 'sort-up');
                }

                $('#' + self.sortColumn()).find('i').show();
                $.each($('.properties-table .sort i'), function (index, value) {
                    $(this).hide();
                });
            }
        });
    };

    this.countContracts = ko.computed(function () {
        return parseInt(self.aContracts().length);
    });

    this.goToPage = function (page) {
        self.current(page);
        if (page == 'First') {
            self.current(1);
        }
        if (page == 'Last') {
            self.current(Math.ceil(self.total() / limit));
        }
        self.ajaxAction();
    };

    this.loadContract = function (contract, type) {
        var index = $.inArray(contract.id, self.needRefresh());
        var id, callback;

        switch (type) {
            case 'edit':
                id = '#tenant-edit-property-popup';
                callback = DetailsViewModel.editContract;
                break;
            case 'approve':
                id = '#tenant-approve-property-popup';
                callback = DetailsViewModel.approveContract;
                break;
            case 'review':
                id = '#tenant-review-property-popup';
                callback = DetailsViewModel.reviewContract;
                break;
        }

        if (index > -1) {
            $('#tenant-edit-property-popup').dialog('close');
            $('#tenant-approve-property-popup').dialog('close');
            $('#tenant-review-property-popup').dialog('close');
            $(id).dialog('open');
            jQuery(id).showOverlay();

            $.ajax({
                url: Routing.generate('landlord_contract_details', {'contractId' : contract.id}),
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    jQuery(id).hideOverlay();
                    if (response.id && typeof callback === 'function') {
                        callback(response);
                    }
                }
            });
        } else {
            self.needRefresh().push(contract.id);
            if (typeof callback === 'function') {
                callback(contract);
            }
        }
    };

    this.editContract = function (data) {
        var position = $('#edit-' + data.id).position();
        data.top = position.top - 300;

        self.loadContract(data, 'edit');
    };
    this.approveContract = function (data) {
        var position = $('#edit-' + data.id).position();
        self.loadContract(data, 'approve');
    };
    this.reviewContract = function (data) {
        var position = $('#edit-' + data.id).position();
        self.loadContract(data, 'review');
    };
    this.addTenant = function () {
        $('#tenant-add-property-popup').dialog('open');
        if ($('.payment-end').val().length > 0) {
            var finish = $('.payment-end').val();
        } else {
            var today = new Date();
            today.setFullYear(today.getFullYear() + 1);
            var finish = today.toString('MM/dd/yyyy');
        }
        if ($('.payment-start').val().length > 0) {
            var start = $('.payment-start').val();
        } else {
            var today = new Date();
            var start = today.toString('MM/dd/yyyy');
        }
        $('.payment-end').val(finish);
        $('.payment-start').val(start);
        $('.payment-start').attr('readonly', true);
        $('.payment-end').attr('readonly', true);
        $('.payment-start, .payment-end').datepicker({
            showOn: "both",
            buttonImage: "/bundles/rjpublic/images/ill-datepicker-icon.png",
            dateFormat: 'm/d/yy',
            minDate: 0
        });
    };
    this.filterAddress = function (data) {
        //console.log(data.id);
    };
}

