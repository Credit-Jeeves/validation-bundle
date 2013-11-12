function searchProperties() {
    this.searchText = ko.observable("");
    this.searchCollum = ko.observable("");
    this.property = ko.observable("");
    this.isSearch =  ko.observable(false);
    var self = this;

    this.searchFunction = function() {
        var searchCollum = $('#searchFilterSelect').linkselect('val');

        if(typeof searchCollum != 'string') {
            searchCollum = '';
        }
        if(self.searchText().length <= 0) {
            $('#search').css('border-color', 'red');
            return;
        } else {
            $('#search').css('border-color', '#bdbdbd');
        }
        self.isSearch(true);
        self.property().searchText(self.searchText());
        self.property().searchCollum(searchCollum);
        self.property().current(1);
        self.property().ajaxAction();
    };

    this.clearSearch = function() {
        self.property().searchText('');
        self.property().searchCollum('');
        self.property().current(1);
        self.property().ajaxAction();
        self.searchCollum('');
        self.searchText('');
        self.isSearch(false);
    };
}