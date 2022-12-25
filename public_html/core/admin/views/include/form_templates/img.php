<div class="vg-wrap vg-element vg-half">
    <div class="vg-wrap vg-element vg-full">
        <div class="vg-element vg-full vg-left">
            <span class="vg-header"><?=$this->translate[$row][0] ?: $row?></span>
        </div>
        <div class="vg-element vg-full vg-left">
            <span class="vg-text vg-firm-color5"></span><span class="vg_subheader">
                <?=$this->translate[$row][1] ?: $row?>
            </span>
        </div>
    </div>
    <div class="vg-wrap vg-element vg-full">
        <label for="<?=$row?>" class="vg-wrap vg-full file_upload vg-left">
            <span class="vg-element vg-full vg-input vg-text vg-left vg-button" style="float: left; margin-right: 10px">Выбрать</span>
            <a style="color:black" href="/admin/delete/goods/53/main_img/ODQwLTg0MDMxNjlfZG93bmxvYWQtc3ZnLWRvd25sb2FkLXBuZy1kb2N0b3ItZW1vamkucG5n" class="vg-element vg-full vg-input vg-text vg-left vg-button vg_delete">
                <span>Удалить</span>
            </a>
            <input id="<?=$row?>" type="file" name="<?=$row?>" class="single_img" accept="image/*,image/jpeg,image/png,image/gif">
        </label>
    </div>
    <div class="vg-wrap vg-element vg-full">
        <div class="vg-element vg-left img_show main_img_show">
            <?php if($this->data[$row]): ?>
                <img src="<?=PATH . UPLOAD_DIR . $this->data[$row]?>">
            <?php endif; ?>
        </div>
    </div>
</div>
