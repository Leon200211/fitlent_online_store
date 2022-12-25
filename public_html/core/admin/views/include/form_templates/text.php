<div class="vg-element vg-full vg-box-shadow">
    <div class="vg-wrap vg-element vg-full vg-box-shadow">
        <div class="vg-wrap vg-element vg-full">
            <div class="vg-element vg-full vg-left">
                <span class="vg-header"><?=$this->translate[$row][0] ?: $row?></span>
            </div>
            <div class="vg-element vg-full vg-left">
                <span class="vg-text vg-firm-color5"></span><span class="vg_subheader"><?=$this->translate[$row][1] ?: $row?></span>
            </div>
        </div>
        <div class="vg-element vg-full">
            <div class="vg-element vg-full vg-left ">
                <input type="text" name="<?=$row?>" class="vg-input vg-text vg-firm-color1"
                       value="<?= isset($_SESSION['res'][$row]) ? htmlspecialchars($_SESSION['res'][$row]) : htmlspecialchars($this->data[$row])?>">
            </div>
        </div>
    </div>
</div>