/**
 * @package     Invoice 
 * @author      Kailash
 */


/**
 * Initilizing Gloabal variables
 */

var _listUrl = '';
var _relativeDate = '';
var _client = '';
var _product = '';
var data = [];


$(document).ready(function () {
    /**
     * Filter Events
     */
    $("body").on('change', 'select[name="relative_date"], select[name="product"], select[name="client"]', function (e) {
        ajaxRequestParameter($(this));
        ajaxCall();

    });
});

/**
 * make Ajax call
 */

function ajaxCall() {
    $.ajax({
        url: $(this).data('href'),
        type: 'POST',
        data: data,
        context: this,
        success: function (result) {
            // Unseting varables
            _relativeDate = _client = _product = '';
            setResponse(result);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert("ERROR: Something Went Wrong!!!!. Please Try again");
        }
    });
}

/**
 * Bind response to html according to filters
 */

function setResponse(result) {
    // success condition
    if (result.code == "200") {
        $("body table tbody").html(result.result.list);
        //If client filter is selected that change product filter accordingly
        setProductDropDown(result.result.products);

    } else {

    }
}

/**
 * Dynamic dropdown values based on client filter selected
 */

function setProductDropDown(products) {
    var option = '<option value="">- -SELECT PRODUCT- -</option>';
    _product = $('select[name="product"]').val();

    $.each(products, function (key, product) {
        if (_product === product.product_id) {
            option += "<option value='" + product.product_id + "' SELECTED>  " + product.product_description + " </option>";
        } else {
            option += "<option value='" + product.product_id + "'>  " + product.product_description + " </option>";
        }
    });



    $('select[name="product"]').find('option').remove().end().append(option);
}

/**
 * Creating Ajax paramaters based on filter selected
 */


function ajaxRequestParameter(e) {
    _relativeDate = $('select[name="relative_date"]').val();
    _product = $('select[name="product"]').val();
    _client = $('select[name="client"]').val();
    if ($(e).attr("name") == "client") {
        _product = '';
    }

    data = {
        'relativeDate': _relativeDate,
        'client': _client,
        'product': _product
    };


}


/**
 * start loading while ajax call
 */
$(document).ajaxStart(function () {
    $("body  .panel").prepend("<div class='overlay'> Loading . . . </div>");
});


/**
 * stop loading while ajax call
 */
$(document).ajaxStop(function () {
    $("body  .panel").find('.overlay').remove();
});
