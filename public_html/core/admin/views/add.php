<form id="main-form" class="vg-wrap vg-element vg-ninteen-of-twenty" method="post" action="/admin/shop/edit" enctype="multipart/form-data">

    <div class="vg-wrap vg-element vg-full vg-firm-background-color4 vg-box-shadow">

        <div class="vg-element vg-half vg-left">
            <div class="vg-element vg-padding-in-px">
                <input type="submit" class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button" value="Сохранить">
            </div>

            <?php if(!$this->noDelete and $this->data): ?>
                <div class="vg-element vg-padding-in-px">
                    <a href="/admin/delete/goods/53" class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button vg-center vg_delete">
                        <span>Удалить</span>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>


    <?php if($this->data): ?>
        <input id="tableId" type="hidden" name="<?=$this->columns['id_row']?>" value="<?=$this->data[$this->columns['id_row']]?>">
    <?php endif; ?>

    <input type="hidden" name="table" value="<?=$this->table?>">


    <?php

        foreach ($this->blocks as $class => $block){

            // если не указано $blockNeedle
            if(is_int($class)) $class = 'vg-content';

            echo '<div class="vg-wrap vg-element ' . $class . '">';

            if($block){
                if($class !== 'vg-content') echo '<div class="vg-full vg-firm-background-color4 vg-box-shadow">';

                foreach ($block as $row){

                    foreach ($this->templateArr as $template => $items){

                        if(in_array($row, $items)){

                            // если не подключен шаблон
                            if(!@include $_SERVER['DOCUMENT_ROOT'] . $this->formTemplates . $template . '.php'){
                                throw new \core\base\exceptions\RouteException("Не найден шаблон " .
                                    $_SERVER['DOCUMENT_ROOT'] . $this->formTemplates . $template . '.php');
                            }

                            // если нашли шаблон останавливаем цикл
                            break;

                        }
                    }
                }

                if($class !== 'vg-content') echo '</div>';
            }


            echo "</div>";
        }

    ?>





    <div class="vg-wrap vg-element vg-full">
        <div class="vg-wrap vg-element vg-full vg-firm-background-color4 vg-box-shadow">
            <div class="vg-element vg-half vg-left">
                <div class="vg-element vg-padding-in-px">
                    <input type="submit" class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button" value="Сохранить">
                </div>
                <div class="vg-element vg-padding-in-px">
                    <a href="/admin/shop/delete/table/shop_products/id_row/id/id/92" class="vg-text vg-firm-color1 vg-firm-background-color4 vg-input vg-button vg-center vg_delete">
                        <span>Удалить</span>
                    </a>
                </div>
            </div>
        </div>
    </div>



</form>
