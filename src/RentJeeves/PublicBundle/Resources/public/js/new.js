$(document).ready(function () {
//TODO in future this must be refactor, because such think much more better build with knockoutjs and I don't like this
// code it's hard to support it
    function initScroll() {
        $('#search-result-text').slimScroll({
            alwaysVisible: true,
            width: 307,
            height: 295
        });
    }

    initScroll();

    var googleLib = $('#property-search').google({
        mapCanvasId: "search-result-map",
        markers: true,
        clearSearchId: 'delete',
        findButtonId: 'search-submit',
        defaultLat: $('#lat').val() ? $('#lat').val() : null,
        defaultLong: $('#lng').val() ? $('#lng').val() : null,
        classError: 'errorsGoogleSearch',
        addPropertyCallback: function (data, textStatus, jqXHR) {
            if (data.hasLandlord) {
                return location.href = Routing.generate('iframe_search_check', {'propertyId': data.property.id});
            }

            $('#search-submit').removeClass('disabled grey');
            $('#search-submit').find('.loadingSpinner').hide();

            $('.search-result-text').find('h4').hide();
            $.each($('.addressText'), function (index, value) {
                $(this).hide();
            });
            var link = Routing.generate('iframe_search_check', {'propertyId': data.property.id});
            $('.notFound').show();
            $('.notFound').find('.titleAddress').html(data.property.number + ' ' + data.property.street);
            $('.notFound').find('.contentAddress').html(data.property.city + ', ' + data.property.area + ' ' + data.property.zip);
            $('.notFound').find('.inviteLandlord').attr('href', link);
            $('.notFound').parent().parent().find('.titleNotFound').show();
            $('.notFound').parent().parent().find('.titleSearch').hide();

            var contentString = this.getHtmlPopap(
                $('.notFound').find('.titleAddress').html(),
                $('.notFound').find('.contentAddress').html()
            );

            contentString += '<hr /><a href="' + link + '" class="button small inviteLandlord" >';
            contentString += '<span>' + Translator.trans('invite.landlord') + '</span></a>';

            var infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            var rentaPoint = new google.maps.MarkerImage('/bundles/rjpublic/images/ill-renta-point_1.png',
                new google.maps.Size(26, 42),
                new google.maps.Point(0, 0),
                new google.maps.Point(13, 42)
            );

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(data.property.jb, data.property.kb),
                map: this.map,
                title: data.property.address,
                icon: rentaPoint,
                shadow: this.rentaPiontShadow
            });


            this.deleteOverlays();
            if ("notfound" in this.markersArray) {
                this.markersArray['notfound'].setMap(null);
            }
            this.markersArray['notfound'] = marker;
            this.infowindow.close();
            google.maps.event.addListener(marker, 'click', function () {
                infowindow.open(this.map, marker);
            });
            infowindow.open(this.map, marker);

        }
    });


    function getUnitName(element) {
        var selectBox = element.find('.select-unit').find(':selected');
        var inputBox = element.find('.unitNew');

        if (inputBox.parent().css('display') !== 'none') {
            return inputBox.val();
        }

        if (selectBox.hasClass('noneField') || selectBox.hasClass('addNewUnit')) {
            return '';
        }

        return selectBox.val();
    }

    function selectProperty(propertyId, isHide) {
        var propertyElement = null;

        if (isHide === false) {
            $.each($('.addressText'), function (index, value) {
                var id = $(this).attr('data');
                if (id != propertyId) {
                    $(this).show();
                } else {
                    $(this).css({backgroundColor: '#FFFFFF'});
                    propertyElement = $(this);
                }
            });
        } else {
            $.each($('.addressText'), function (index, value) {
                var id = $(this).attr('data');
                if (id != propertyId) {
                    $(this).hide();
                } else {
                    $(this).css({backgroundColor: '#EEEEEE'});
                    propertyElement = $(this);
                }
            });
        }

        return propertyElement;
    }

    function selectUnit(propertyElement, unitName) {
        var isSelected = false;
        $.each(propertyElement.find('.select-unit option'), function (index, value) {
            if ($(value).attr('value') === unitName) {
                $(this).attr("selected", "selected");
                isSelected = true;
            }
        });

        if (isSelected) {
            return;
        }

        propertyElement.find('.createNewUnit').show();
        propertyElement.find('.lab1').show();
        propertyElement.find('.lab2').hide();
        propertyElement.find('div>.selectUnit').hide();
        propertyElement.find('.unitNew').val(unitName);
    }

    $('.unitNew').change(function () {
        $('.FormUnitName').val(getUnitName($(this).parent().parent()));
    });

    $('.select-unit').change(function () {
        val = $(this).val();
        if (val === 'new') {
            $(this).parent().hide();
            $(this).parent().parent().find('.createNewUnit').show();
            $(this).parent().parent().find('.lab1').show();
            $(this).parent().parent().find('.lab2').hide();
            $('.FormUnitName').val('');
        }

        if (val === 'none') {
            $('.FormUnitName').val('');
        }

        $('.FormUnitName').val(getUnitName($(this).parent().parent().parent()));
    });

    $('.see-all').click(function () {
        $(this).parent().parent().find('.selectUnit').show();
        $(this).parent().hide();
        $(this).parent().parent().find('.select-unit:selected').prop("selected", false);
        $(this).parent().parent().find('.noneField').attr('selected', true);
        $(this).parent().parent().find('.lab2').show();
        $(this).parent().parent().find('.lab1').hide();
        $('.FormUnitName').val('');
        return false;
    });

    $('#register').click(function () {
        var propertyId = $('#propertyId').val();
        if (propertyId == '') {
            $('html, body').animate({
                scrollTop: $(".search-box").offset().top
            }, 800);
            googleLib.showError(Translator.trans('select.rental'));
            return false;
        }
    });


    $('.thisIsMyRental').click(function () {
        if ($(this).hasClass('match')) {
            propertyId = $(this).attr('data');
            selectProperty(propertyId, isHide = false);
            $('.FormPropertyId').val('');
            $('.FormUnitName').val('');
            $('#register').addClass('greyButton');
            initScroll();
            $(this).removeClass('match');
        } else {
            propertyId = $(this).attr('data');
            selectProperty(propertyId, isHide = true);
            $('.FormPropertyId').val(propertyId);
            $('.FormUnitName').val(getUnitName($(this).parent().parent().parent()));
            $('#register').removeClass('greyButton');
            initScroll();
            $(this).addClass('match');
            focusInputWithErrorOrFirstNameInput();
        }

        return false;
    });

    $(function () {
        $("#pricing-popup").dialog({
            width: 660,
            autoOpen: false,
            modal: true
        });
    });

    $('#popup-pricing').click(function () {
        $("#pricing-popup").dialog('open');
    });

    $('#pricing-popup button.button-close').click(function () {
        $("#pricing-popup").dialog('close');
    });

    function initSelectedOption() {
        var propertyId = $('.FormPropertyId').val();
        var unitName = $('.FormUnitName').val();

        if (propertyId === undefined || propertyId.length === 0) {
            return;
        }

        var propertyElement = selectProperty(propertyId, isHide = true);
        if (propertyElement === null || unitName.length === 0) {
            return;
        }

        selectUnit(propertyElement, unitName);
    }

    initSelectedOption();

    // If the form is not valid - move focus
    // so that users could see errors
    if ($('form#formNewUser ul.error_list').length > 0) {
        if ($('div.type1>ul.error_list').length === 0) {
            $('.thisIsMyRental').click();
        } else {
            focusInputWithErrorOrFirstNameInput();
        }
    }

    function focusInputWithErrorOrFirstNameInput() {
        var fieldBoxes = $('form#formNewUser div.fields-box').has('ul.error_list');
        if (fieldBoxes.length > 0) {
            fieldBoxes.first().find('input').focus();
        } else {
            $('#rentjeeves_publicbundle_tenanttype_first_name').focus();
        }
    }
});
