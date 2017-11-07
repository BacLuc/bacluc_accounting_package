<?php

/**
 * @var \Concrete\Package\BaclucAccountingPackage\Block\BaclucBalanceBlock\Controller $controller
 */
?>

<div class="row bacluc-income-sheet-block" id="<?php echo $htmlid; ?>">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-6 col-sm-4 ">
                <?php echo t("Income Sheet between:"); ?>

            </div>
            <div class="col-xs-6 col-sm-4 ">
                <?php
                echo t("%s and %s", $startDateField->getTableView(), $endDateField->getTableView());
                ?>

            </div>
        </div>
        <form action="<?php echo $this->action('set_range'); ?>" method="POST">
            <div class="row">
                <div class="col-xs-12 col-md-5">
                    <div class="row">
                        <div class="col-xs-6 col-sm-4 ">
                            <?php echo $startDateField->getLabel(); ?>

                        </div>

                        <div class="col-xs-6 col-sm-4">
                            <?php echo $startDateField->getInputHtml($form, true); ?>

                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-5">
                    <div class="row">
                        <div class="col-xs-6 col-sm-4 ">
                            <?php echo $endDateField->getLabel(); ?>

                        </div>

                        <div class="col-xs-6 col-sm-4">
                            <?php echo $endDateField->getInputHtml($form, true); ?>

                        </div>
                    </div>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-2 ">
                    <?php echo $form->submit('submit', t("set Year")); ?>

                </div>

            </div>
        </form>

        <div class="row">
            <div class="col-xs-12 col-md-6 balancerow debitors">
                <div class="row">
                    <div class="col-xs-12">

                        <h3 class="balance-title"><?php echo t("Revenue") ?></h3>
                    </div>

                </div>
                <?php

                $totalRevenue = 0;
                if (count($revenues) > 0) {
                    foreach ($revenues as $name => $amount) {
                        $totalRevenue += $amount;
                        $amount = number_format($amount, 2);
                        print(" 
                        <div class='row'>
                            <div class='col-xs-6 '>
                                <span class='accountname'>$name</span>
                            </div>
                            <div class='col-xs-6 '>
                                <span class='accountvalue number'>$amount</span>
                            </div>
                            
                        </div>
                        ");


                    }
                }

                ?>

                <div class="row total-row">
                    <div class='col-xs-6 total-name-cell'>
                        <span class='total total-name'><?php echo t("Total revenue") ?></span>
                    </div>
                    <div class='col-xs-6 total-amount-cell'>
                        <span class='total total-amount number'><?php echo number_format($totalRevenue, 2); ?></span>
                    </div>


                </div>


            </div>


            <div class="col-xs-12 col-md-6 balancerow creditors">
                <div class="row">
                    <div class="col-xs-12">

                        <h3 class="balance-title"><?php echo t("Expenses") ?></h3>
                    </div>

                </div>
                <?php

                $totalExpenses = 0;
                if (count($expenses) > 0) {
                    foreach ($expenses as $name => $amount) {
                        $totalExpenses += $amount;
                        $amount = number_format($amount, 2);
                        print(" 
                        <div class='row'>
                            <div class='col-xs-6 '>
                                <span class='accountname'>$name</span>
                            </div>
                            <div class='col-xs-6 '>
                                <span class='accountvalue number'>$amount</span>
                            </div>
                            
                        </div>
                        ");

                    }
                }

                ?>

                <div class="row total-row">
                    <div class='col-xs-6 total-name-cell'>
                        <span class='total total-name'><?php echo t("Total expenses") ?></span>
                    </div>
                    <div class='col-xs-6 total-amount-cell'>
                        <span class='total total-amount number'><?php echo number_format($totalExpenses, 2); ?></span>
                    </div>


                </div>


            </div>

        </div>
        <div class="row sumrow">
            <div class="col-xs-12 col-md-6 balancerow">
                <div class="row">
                    <div class='col-xs-6'>
                        <span class='total total-name'><?php echo t("Net Income") ?></span>
                    </div>
                    <div class='col-xs-6 '>
                        <span class='total total-amount number'><?php echo number_format($totalRevenue, 2); ?></span>
                    </div>

                </div>
                <div class="row">
                    <div class='col-xs-6 '>
                        <span class='total total-name'><?php echo t("Total expenses") ?></span>
                    </div>
                    <div class='col-xs-6 '>
                        <span class='total total-amount number'>-<?php echo number_format($totalExpenses, 2); ?></span>
                    </div>

                </div>
                <div class="row total-row">
                    <div class='col-xs-6 '>
                        <span class='total total-name'><?php echo t("Total income") ?></span>
                    </div>
                    <div class='col-xs-6 total-amount-cell'>
                        <span class='total total-amount number'><?php echo number_format(($totalRevenue
                                                                                          - $totalExpenses),
                                2); ?></span>
                    </div>

                </div>
            </div>

        </div>


    </div>

</div>