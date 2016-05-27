function Contracts() {
    var limit = 10;
    var current = 1;
    var self = this;
    var idProperty = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_property';
    var idUnit = '#rentjeeves_landlordbundle_invitetenantcontracttype_contract_unit';
    this.aContracts = ko.observableArray([]);
    this.agentContracts = ko.observableArray([]);
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
                self.agentContracts(response.agent_contracts);
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
        var id, callback, errors;

        switch (type) {
            case 'edit':
                id = '#tenant-edit-property-popup';
                callback = DetailsViewModel.editContract;
                errors = DetailsViewModel.errorsEdit;
                break;
            case 'approve':
                id = '#tenant-approve-property-popup';
                callback = DetailsViewModel.approveContract;
                errors = DetailsViewModel.errorsApprove;
                break;
            default:
                console.log('Unexpected type "' + type + '", loading contract can work only with edit and approve');
                return false;
        }

        if (self.needRefresh().indexOf(contract.id) > -1) {
            $(".ui-dialog-content").dialog("close");
            $(id).dialog('open');
            errors([]);
            $(id).showOverlay();

            $.ajax({
                url: Routing.generate('landlord_contract_details', {'contractId' : contract.id}),
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    $(id).hideOverlay();
                    if (response.id && typeof callback === 'function') {
                        callback(response);
                    } else {
                        errors.push(Translator.trans('error.contract_details.loading'));
                    }
                },
                error: function (xhr, status) {
                    errors.push(Translator.trans('error.contract_details.loading'));
                }
            });
        } else {
            if (typeof callback === 'function') {
                callback(contract);
            }
        }
    };

    this.editContract = function (data) {
        console.log(data);
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
        DetailsViewModel.reviewContract(data);
    };

    this.addTenant = function () {
        this.getUnits($(idProperty).linkselect('val'));
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

    /**
     * Get all units for current group + property
     * and
     * create linkselect for widget "Unit"
     *
     * @param propertyId - integer
     */
    this.getUnits = function (propertyId) {
        $('#unitLoading').show();

        $.ajax({
            url: Routing.generate('landlord_units_list'),
            type: 'POST',
            dataType: 'json',
            data: {'property_id': propertyId},
            success: function (response) {
                $('#unitLoading').hide();

                if (response.units.length == 0 || response.isSingle == true) {
                    $(idUnit).linkselect('destroy');
                    $(idUnit).html(' ');
                    $(idUnit).linkselect();
                    $('#rentjeeves_landlordbundle_invitetenantcontracttype_contract_unit_link').hide();
                    
                    return;
                }

                var html = '';
                $.each(response.units, function () {
                    var id = $(this).get(0).id;
                    var name = $(this).get(0).name;
                    html += '<option value="' + id + '">' + name + '</option>';
                });

                $(idUnit).linkselect('destroy');
                $(idUnit).html(html);
                $(idUnit).linkselect();
            }
        });
    };

    this.getLinkForAgentGroup = function() {
        return Routing.generate(
            'landlord_tenants_filter',
            {
                'searchText': self.searchText(),
                'searchColumn':self.searchCollum(),
            }
        );
    }

    this.changeGroup = function(item)
    {
        self.aContracts([]);
        self.notHaveResult(false);
        self.processLoading(true);
        console.info(item);
        $.ajax({
            url: Routing.generate('landlord_group_set'),
            type: 'POST',
            dataType: 'json',
            data: {'group_id': item.id},
            success: function () {
                window.location.href = self.getLinkForAgentGroup();
            }
        });

        return false;
    }
}

