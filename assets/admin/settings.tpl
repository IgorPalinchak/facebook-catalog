<div class="container">
    <section class="mini-layout">
        <div class="frame_title clearfix">
            <div class="pull-left">
                <span class="help-inline"></span>
                <span class="title">{lang('Настройки соединения с аккаунтом FaceBook', 'facebook_feed')}</span>
            </div>
            <div class="pull-right">
                <div class="d-i_b">
                    <span class="help-inline"></span>
                    <a href="{$BASE_URL}admin/components/modules_table" class="t-d_n m-r_15 ">
                        <span class="f-s_14">←</span>
                        <span class="t-d_u">{lang('Back', 'facebook_feed')}</span>
                    </a>

                    <a class="btn btn-small " href="{$BASE_URL}admin/components/cp/facebook_feed/facebook_catalogs">
                        <i class="icon-wrench"></i>
                        {lang('Каталоги FaceBook в аккаунте', 'facebook_feed')}
                    </a>


                    <button type="button"
                            class="btn btn-small btn-primary action_on formSubmit"
                            data-form="#wishlist_settings_form"
                    >
                        <i class="icon-ok"></i>{lang('Save', 'facebook_feed')}
                    </button>
                </div>
            </div>
        </div>

        <div class="row-fluid m-t_20">

            <form method="post" action="{site_url('admin/components/cp/facebook_feed/save_main_settings')}"
                  class="form-horizontal"
                  id="wishlist_settings_form">

                <div class="span12">
                    <table class="table table-striped table-bordered table-hover table-condensed t-l_a">
                        <thead>
                        <tr>
                            <th colspan="6">
                                {lang('Настройка соединения', 'facebook_feed')}
                            </th>
                            {if $error && $error != null}
                                <th colspan="6">
                                </th>
                            {/if}
                        <tr>

                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6">
                                <div class="inside_padd">
                                    <br/>

                                    <div class="control-group">
                                        <label class="control-label"
                                               for="main_setting[access_token]">{lang('Токен доступа (получить тут  https://developers.facebook.com/tools/accesstoken , и продлить (после продленя действует 60 дней, потом опять нужно продлить))', 'facebook_feed')}
                                            :</label>
                                        <div class="controls">
                                            <input name="main_setting[access_token]" id="main_setting[access_token]"
                                                   value="{$settings['access_token']}" type="text"/>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <label class="control-label"
                                               for="main_setting[app_secret]">{lang('Секретный ключ приложения  (https://developers.facebook.com/apps/IDAPPLICATION/settings/basic/)', 'facebook_feed')}
                                            :</label>
                                        <div class="controls">
                                            <input name="main_setting[app_secret]" id="main_setting[app_secret]"
                                                   value="{$settings['app_secret']}" type="text"/>
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <label class="control-label"
                                               for="main_setting[app_id]">{lang('ID приложения  (https://developers.facebook.com/apps/IDAPPLICATION/settings/basic/)', 'facebook_feed')}
                                            :</label>
                                        <div class="controls">
                                            <input name="main_setting[app_id]" id="main_setting[app_id]"
                                                   value="{$settings['app_id']}" type="text"/>
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <label class="control-label"
                                               for="main_setting[id]">{lang('ID Business Manager', 'facebook_feed')}
                                            :</label>
                                        <div class="controls">
                                            <input name="main_setting[id]" id="main_setting[id]"
                                                   value="{$settings['id']}" type="text"/>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            {if $error && $error != null}
                                <td>
                                    <span class="error">ERROR: {echo $error}</span>
                                    <br/>
                                    <button type="button"
                                            class="btn  saveSettings">
                                        <i class="icon-ok"></i>{lang('Проверить данные', 'facebook_feed')}
                                    </button>
                                    <br/>
                                    <br/>
                                </td>
                            {/if}
                        </tr>

                        </tbody>
                    </table>
                </div>

                {form_csrf()}
            </form>

        </div>
    </section>
</div>

