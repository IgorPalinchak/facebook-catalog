$(document).ready(
    function () {
        $('body').on(
            'click',
            'table.variablesTable .editVariable',
            function () {
                var editor = $(this).closest('tr').find('div.catalogName');

                var editValue = $.trim(editor.text());
                editor.empty();
                editor.parent().find('.catalogNameEdit').css('display', 'block').val(editValue);
                // editor.parent().find('.priceMainEdit').chosen();

                var editor1 = $(this).closest('tr').find('#idshop');
                val1 = editor1.val();
                aaa = '#catsList' + val1 + '_chosen';
                editor1.parent().find(aaa).remove(aaa);
                editor1.parent().find('.catsEditdiv').css('display', 'block');
                editor1.parent().find('.catsEdit').chosen();


                var editor2 = $(this).closest('tr').find('.activeCity');
                editor2.css('display', 'none');


                $(this).css('display', 'none');
                $(this).closest('tr').find('.refreshVariable').css('display', 'block');

            }
        );

        $('body').on(
            'click',
            '.addVariable',
            function () {
                $('.addVariableContainer').show();
                $(".chosen-add-category").chosen();
                // $(".niceCheck").niceCheck();
                $(this).hide();
            }
        );
    }
);

var providerVariables = {

    delete: function (variable, curElement, locale) {

        $.ajax(
            {
                type: 'POST',
                data: {
                    variable: variable
                },
                url: '/admin/components/cp/facebook_feed/deleteCatalog/',
                beforeSend: function () {
                    showLoading();
                },
                success: function (data) {
                    if (!data) {
                        showMessage(lang('Error'), lang('Variable is not removed'), 'r');
                        return false;
                    }
                    curElement.closest('tr').remove();
                    showMessage(lang('Message'), lang('Variable successfully removed'));
                }
            }
        );
    },
    update: function (curElement, id) {
        var closestTr = curElement.closest('tr');
        var variable = closestTr.find('.catalogNameEdit');
        var variableCatsEdit = curElement.closest('tr').find('.catsEdit');
        var data = [];

        variableCatsEdit.find(':selected').each(function () {
            data.push({value: $(this).val()});
        });
        console.log(data)
        // alert(id);
        // alert($.trim(variable.val()));
        this.validateVariable(variable.val(), data);

        $.ajax(
            {
                type: 'POST',
                data: {
                    id_catalog_shop: id,
                    variable: $.trim(variable.val()),
                    shop_cats: data
                },
                url: '/admin/components/cp/facebook_feed/updateCatalog/',
                beforeSend: function () {
                    showLoading();
                },
                success: function (data) {
                    if (!data) {
                        showMessage(lang('Error'), lang('Variable is not updated'), 'r');
                        return false;
                    }

                    showMessage(lang('Message'), lang('Catalog successfully updated'));
                    window.location.reload()
                }
            }
        );
    },
    add: function (curElement, locale) {
        var variable = curElement.closest('tr').find('.variableEdit');
        var variableCatsEdit = curElement.closest('tr').find('.chosen-add-category');
        // var variableTranslitEdit = curElement.closest('tr').find('.variableTranslitEdit');

        var data = [];
        variableCatsEdit.find(':selected').each(function () {
            data.push({value: $(this).val()});
        });
        console.log(data)

        // $('.chosen-add-category').find(':selected').val()


        this.validateVariable(variable.val(), data);

        $.ajax(
            {
                type: 'POST',
                data: {
                    variable: $.trim(variable.val()),
                    // variableTranslitEdit: $.trim(variableTranslitEdit.val()),
                    variableCatsEdit: data,

                },
                url: '/admin/components/cp/facebook_feed/addCatalog/',
                beforeSend: function () {
                    showLoading();
                },
                success: function (data) {

                    if (!data) {
                        showMessage(lang('Error'), lang('Variable is not added'), 'r');
                        return false;
                    }
                    curElement.parent('div').find('.typeVariable').val('');
                    $('.addVariableContainer').css('display', 'none');
                    $('.addVariableContainer').find('input').val('');
                    $('.addVariable').show();
                    $(data).insertBefore('table.variablesTable .addVariableContainer');
                    showMessage(lang('Message'), lang('Variable successfully added'));
                    window.location.reload()
                }
            }
        );
    },
    updateVariablesList: function (curElement, template_id, locale) {
        if (!curElement.hasClass('active')) {
            $.ajax(
                {
                    type: 'POST',
                    data: {
                        template_id: template_id
                    },
                    url: '/admin/components/cp/facebook_feed/getTemplateVariables/' + locale,
                    success: function (data) {
                        $('#userMailVariables').html(data);
                        $('#adminMailVariables').html(data);
                    }
                }
            );
        }
    },
    validateVariable: function (variable, variableValue) {
        var variable = $.trim(variable);
        // var variableValue = $.trim(variableValue);
        var variableValue = $.trim(variableValue);

        if (!variable) {
            showMessage(lang('Error'), lang('Enter variable'), 'r');
            exit;
        }

        // if (variableValue.match(/[а-яА-Я]{1,}/)) {
        //     showMessage(lang('Error'), lang('Variable should contain only Latin characters'), 'r');
        //     exit;
        // }
        if (!variableValue || variableValue == 'false') {
            showMessage(lang('Error'), lang('Category must have a value'), 'r');
            exit;
        }
    }
};

$(document).on('click', '[data-update="to_catalog"]', function () {
    var button = $(this);
    editor1 = $(this).closest('tr').find('#idshop');
    val1 = editor1.val();
    var active_cat = button.hasClass('disable_tovar') ? 1 : 0;

    $.ajax(
        {
            type: 'POST',
            data: {
                variable: val1,
                active_cat: active_cat,
            },
            url: '/admin/components/cp/facebook_feed/activateCatalog/',
            beforeSend: function () {
                showLoading();
            },
            success: function (data) {

                if (!data) {
                    showMessage(lang('Error'), lang('Catalog is not activated'), 'r');
                    return false;
                }
                showMessage(lang('Message'), lang('Feed successfully changed'));
                window.location.reload()

            }

        }
    );

});
