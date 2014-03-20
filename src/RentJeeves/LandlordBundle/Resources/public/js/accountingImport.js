function accountingImport() {
    var self = this;
    this.pagesList = ko.observableArray([]);
    this.pagesTotal = ko.observable(0);
    this.currentPage = ko.observable(1);
    this.errorLoadDataMessage = ko.observable('');
    this.isProcessing = ko.observable(false);
    this.rows =  ko.observableArray([]);
    this.loadData = function() {
        self.isProcessing(true);
        self.rows([]);
        self.pagesList([]);
        self.pagesTotal(0);
        $.ajax({
            url: Routing.generate('landlord_reports_review_get_rows'),
            type: 'POST',
            dataType: 'json',
            data: {
                'page' : self.currentPage()
            },
            success: function(response) {
                self.isProcessing(false);
                self.errorLoadDataMessage(response.message);
                if (response.error === false) {
                    self.rows(response.rows);
                    self.pagesList(response.pagination);
                    self.pagesTotal(response.total);
                    self.initGuiScript();
                    return;
                }


            }
        });
    };

    this.initGuiScript = function() {

    };

    this.goToPage = function(page) {
        self.currentPage(page);
        self.loadData();
    };

    this.getStatusText = function(data) {
        if (data.isSkipped) {
            return Translator.trans('import.status.skip');
        }

        if (data.Tenant.contracts[0].status == 'finished') {
            return Translator.trans('import.status.ended');
        }

        if (data.Tenant.contracts[0].id !== undefined && data.Tenant.id !== undefined) {
            return Translator.trans('import.status.match');
        }

        return Translator.trans('import.status.new');
    };

    this.formatDate = function(dateString) {
        if (!dateString || dateString.length <= 0) {
            return '';
        }
        var date = new Date(dateString);
        return (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
    }

    this.isProcessing.subscribe(function(newValue) {
        if (newValue) {
            //$('table').parent().showOverlay();
            return;
        }
        //$('table').parent().hideOverlay();
    });
}
