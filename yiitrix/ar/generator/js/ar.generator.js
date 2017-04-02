setTimeout(function(){
    $(function(){
        $('#edit1_edit_table').prepend($('<tr><td width="40%" class="adm-detail-content-cell-l">Класс модели:</td><td width="60%" class="adm-detail-content-cell-r">' +
            '<input type="text" name="ar_iblock_class_name" id="ar_iblock_class_name" size="20" >' +
            '&nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="ar_iblock_rewrite_model"/> Перзаписать модель</label>' +
        '</td></tr>'));

        if (window.arIblockClassName && window.arIblockClassName.length != 0) {
            $('#ar_iblock_class_name').val(window.arIblockClassName)/*.attr('disabled', 'disabled')*/;
        }
    });
}, 1000);
