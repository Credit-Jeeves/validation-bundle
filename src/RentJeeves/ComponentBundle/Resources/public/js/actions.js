function Actions() {
    var limit = 10;
    var current = 1;
    var self = this;
    this.aActions = ko.observableArray([]);
    this.pages = ko.observableArray([]);
    this.total = ko.observable(0);
    this.current = ko.observable(1);
    this.processActions = ko.observable(true);
    this.sortColumn = ko.observable("statusA");
    this.isSortAsc = ko.observable(false);
    this.searchText = ko.observable("");
    this.searchCollum = ko.observable("");
    this.isSearch = ko.observable(false);
    this.notHaveResult = ko.observable(false);

    this.search = function () {
        var searchCollum = $('#searchActions').linkselect('val');
        if (typeof searchCollum != 'string') {
            searchCollum = '';
        }
        if (self.searchText().length <= 0) {
            $('#searsh-field-actions').css('border-color', 'red');
            return;
        } else {
            $('#searsh-field-actions').css('border-color', '#bdbdbd');
        }
        self.isSearch(true);
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

    this.ajaxAction = function () {
        self.processActions(true);
        self.notHaveResult(false);
        self.aActions([]);
        $.ajax({
            url: Routing.generate('landlord_actions_list'),
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
                self.processActions(false);
                if (!response || !response.actions) {
                    self.notHaveResult(true);
                    return;
                }
                self.processActions(false);
                self.aActions([]);
                self.aActions(response.actions);
                self.total(response.total);
                self.pages(response.pagination);
                if (0 >= self.aActions().length) {
                    self.notHaveResult(true);
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
                $.each($('#actions-block .sort i'), function (index, value) {
                    $(this).hide();
                });
            }
        });
    };
    this.countActions = ko.computed(function () {
        return parseInt(self.aActions().length);
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
    this.resolve = function(data) {
        var dialogModel = null;

        if ('CONTRACT ENDED' === data.status) {
            dialogModel = new ResolveEnded(self, data);
        } else {
            dialogModel = new ResolveLate(self, data);
        }
    };
    this.sortIt = function (data, event) {
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

    this.ajaxAction();
}
