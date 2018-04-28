<div class="container">


    <section class="mini-layout">
        <div class="frame_title clearfix">
            <div class="pull-left">
                <span class="help-inline"></span>
                <span class="title">{lang('Список каталогов', 'facebook_feed')}</span>
            </div>
            <div class="pull-right">
                <div class="d-i_b">
                    <a href="{$BASE_URL}admin/components/cp/facebook_feed/" class="t-d_n m-r_15 pjax">
                        <span class="f-s_14">←</span>
                        <span class="t-d_u">{lang('Go back', 'facebook_feed')}</span>
                    </a>
                </div>


            </div>
        </div>
        <div class="tab-pane" id="variables">
            <div class="inside_padd">
                {if $error && $error != null}
                {lang('Сверится с FaceBook невозможно. Проверте настройки модуля', 'facebook_feed')}
                {else:}
                <table class="table  table-bordered table-hover table-condensed variablesTable t-l_a">
                    <thead>
                    <th>{lang('Название каталога', 'facebook_feed')}</th>
                    <th>{lang('ИД каталога', 'facebook_feed')}</th>
                    <th>{lang('Категории магазина для каталога', 'facebook_feed')}</th>
                    <th>{lang('Активен в магазине', 'facebook_feed')}</th>
                    <th>{lang('Edit', 'facebook_feed')}</th>
                    <th>{lang('Delete', 'facebook_feed')}</th>
                    </thead>


                    {foreach $all_catalogs as $id => $catalog}
                        <tr>
                            <td class="span5">
                                <div class="catalogName">
                                    {echo  $catalog['catalog_name']}
                                </div>

                                <input type="text" name="catalogNameEdit" class="catalogNameEdit"
                                       style="display: none"/>
                            </td>
                            <td class="span5">
                                <div class="">
                                    {echo  $catalog['catalog_id']}{if !$catalog['exist_in_facebook']} какталога нет не FaceBook {/if}
                                </div>
                                {/*}
                                <input type="text" name="cityTranslitNameEdit" class="cityTranslitNameEdit"
                                       style="display: none"/>{ */}
                            </td>
                            <td class="span5">
                                <input type="hidden" id="idshop" value="{echo $id}"/>
                                <select name="catsList" id="catsList{echo $id}" class="catsList{echo $id}" disabled
                                        {/*}style="display: none" { */} multiple >
                                <option value="none"
                                        {if in_array('none',$catalog['categories'])}selected="selected"{/if}>Нет
                                </option>
                                <option value="all"
                                        {if in_array('all',$catalog['categories'])}selected="selected"{/if}>Все
                                </option>
                                {foreach $shop_categories as $categor}
                                    <option value="{echo $categor->getId()}"
                                            {if in_array($categor->getId(), $catalog['categories'])}selected="selected"{/if}
                                    >
                                        {str_repeat(' - ',count(unserialize($categor->getFullPathIds())))}
                                        {echo $categor->getName()}
                                    </option>
                                {/foreach}
                                </select>

                                <div class="catsEditdiv" style="display: none">
                                    <select name="catsEdit[]" id="catsEdit" class="catsEdit"
                                            multiple>
                                        <option value="none"
                                                {if in_array('none',$catalog['categories'])}selected="selected"{/if}>Нет
                                        </option>
                                        <option value="all"
                                                {if in_array('all',$catalog['categories'])}selected="selected"{/if}>Все
                                        </option>
                                        {foreach $shop_categories as $category}
                                            <option value="{echo $category->getId()}"
                                                    {if in_array($category->getId(), $catalog['categories'])}selected="selected"{/if}
                                            >
                                                {str_repeat(' - ',count(unserialize($category->getFullPathIds())))}
                                                {echo $category->getName()}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            </td>
                            <td class="span5">
                                <div class="activeCity">
                                    {if $catalog['exist_in_facebook']}
                                        <div class="frame_prod-on_off">
                                            <span data-id="{echo $id}"
                                                  data-update="to_catalog"
                                                  class="prod-on_off{if $catalog['active'] !='1'} disable_tovar{/if}"
                                            >
                                            </span>
                                        </div>
                                        {if $catalog['active'] =='1'}
                                            {lang('Номер фида', 'facebook_feed')}
                                            {echo $catalog['feed_id']}
                                        {else:}
                                            {lang('Фид не создан, или удален', 'facebook_feed')}
                                        {/if}
                                    {/if}
                                </div>
                            </td>
                            <td style="width: 100px">
                                {if $catalog['exist_in_facebook']}
                                    <button class="btn my_btn_s btn-small editVariable" type="button">
                                        <i class="icon-edit"></i>
                                    </button>
                                {/if}
                                <button data-update="count"
                                        onclick="providerVariables.update($(this),  '{echo $id}')"
                                        class="btn btn-small refreshVariable my_btn_s" type="button"
                                        style="display: none;">
                                    <i class="icon-ok"></i>
                                </button>
                            </td>
                            <td class="span1">
                                <button class="btn my_btn_s btn-small btn-danger " type="button"
                                        onclick="providerVariables.delete('{echo $id}', $(this))">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                    <tr class="addVariableContainer" style="display: none">
                        <td class="span5">
                            <input type="text" name="variableEdit" class="variableEdit"/>

                        </td>
                        <td class="span5">
                            {/*} <input type="text" name="variableTranslitEdit" class="variableTranslitEdit"/>{ */}
                            Данные будут возвращены от FaceBook

                        </td>
                        <td class="span5">
                            <select name="variableCatsEdit[]" id="variableCatsEdit"
                                    class="chosen-add-category variableCatsEdit" multiple>
                                <option value="none">Нет</option>
                                <option value="all">Все</option>
                                {foreach $shop_categories as $category}
                                    <option value="{echo $category->getId()}">{str_repeat(' - ',count(unserialize($category->getFullPathIds())))} {echo $category->getName()}</option>
                                {/foreach}
                            </select>
                        </td>

                        {/*}
                        <td class="span5">

                            <input type="checkbox" class="variableValueEdit niceCheck" id="variableValueEdit"/>

                        </td>
                        { */}
                        <td style="width: 100px" colspan="2">
                            <button data-update="count"
                                    onclick="providerVariables.add($(this), '{echo $locale}');"
                                    data-variable="" class="btn btn-small" type="button"
                                    style="display: block; margin-top: 4px;margin-left: 4px">
                                <i class="icon-plus"></i>
                            </button>
                        </td>
                    </tr>
                </table>

                <button class="btn btn-small btn-success addVariable">
                    <i class="icon-plus icon-white"></i>&nbsp;{lang('Добавить новый каталог', 'facebook_feed')}
                </button>
                {/if}
            </div>
        </div>
</div>
</section>
</div>