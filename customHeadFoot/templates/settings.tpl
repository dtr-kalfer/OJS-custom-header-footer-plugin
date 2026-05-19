{* Smarty template for customHeadFoot plugin settings inside an OJS modal wrapper *}

<script>
        $(function() {ldelim}
                $('#customHeadFootSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});
</script>

<form class="pkp_form" id="customHeadFootSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
        {csrf}

        <table class="pkp_table" style="width:100%;">
                <tr>
                        <td style="padding: 8px;"><label for="headerBackgroundImage">Header Background Image URL</label></td>
                        <td style="padding: 8px;"><input type="text" name="headerBackgroundImage" id="headerBackgroundImage" value="{$headerBackgroundImage|escape}" size="40" class="textField" /></td>
                </tr>
                <tr>
                        <td style="padding: 8px;"><label for="headerOpacity">Header Opacity (0.0 - 1.0)</label></td>
                        <td style="padding: 8px;"><input type="text" name="headerOpacity" id="headerOpacity" value="{$headerOpacity|escape}" size="5" class="textField" /></td>
                </tr>
                <tr>
                        <td style="padding: 8px;"><label for="footerBackgroundImage">Footer Background Image URL</label></td>
                        <td style="padding: 8px;"><input type="text" name="footerBackgroundImage" id="footerBackgroundImage" value="{$footerBackgroundImage|escape}" size="40" class="textField" /></td>
                </tr>
                <tr>
                        <td style="padding: 8px;"><label for="footerOpacity">Footer Opacity (0.0 - 1.0)</label></td>
                        <td style="padding: 8px;"><input type="text" name="footerOpacity" id="footerOpacity" value="{$footerOpacity|escape}" size="5" class="textField" /></td>
                </tr>
        </table>

        <p style="text-align: right; margin-top: 15px;">
                <input type="submit" name="save" class="button defaultButton" value="{"common.save"|translate}" />
        </p>
</form>