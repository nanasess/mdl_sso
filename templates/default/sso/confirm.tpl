<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="パスワードを忘れた方(入力ページ)"}-->
<div id="undercolumn">
    <div id="undercolumn_entry">
        <h2 class="title"><!--{$tpl_title|h}--></h2>
        <p>下記の内容で送信してもよろしいでしょうか？<br />
            よろしければ、一番下の「会員登録をする」ボタンをクリックしてください。</p>
        <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="complete">
            <!--{foreach from=$arrForm key=key item=item}-->
            <input type="hidden" name="<!--{$key|h}-->" value="<!--{$item.value|h}-->" />
            <!--{/foreach}-->

            <table summary="入力内容確認">
                <!--{include file="`$smarty.const.TEMPLATE_REALDIR`frontparts/form_personal_confirm.tpl" flgFields=2 prefix=""}-->
            </table>

            <div class="btn_area">
                <ul>
                    <li>
                        <a href="?" onclick="eccube.setModeAndSubmit('return', '', ''); return false;">
                            <img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_back.jpg" alt="戻る" />
                        </a>
                    </li>
                    <li>
                        <input type="image" class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_entry.jpg" alt="会員登録をする" name="send" id="send" />
                    </li>
                </ul>
            </div>

        </form>
    </div>
</div>
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_footer.tpl"}-->
