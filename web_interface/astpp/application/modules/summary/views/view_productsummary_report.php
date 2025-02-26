<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        $(this).css('background-color', 'Green');
        build_grid("productsummary_grid","",<? echo $grid_fields; ?>,<? echo $grid_buttons; ?>);
        
        $("#customersummary_search_btn").click(function(){
            document.productsummary_search.submit();
        });
        $("#id_reset").click(function(){
            clear_search_request("productsummary_grid","");
            window.location="<? echo base_url() ?>summary/product_clearsearchfilter/";
        });
        $("#from_date").datetimepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd HH:mm:ss',
            footer:true,
            
         });  
         $("#to_date").datetimepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            modal:true,
            format: 'yyyy-mm-dd HH:mm:ss',
            footer:true
         }); 
    });
</script>
<? endblock() ?>

<? startblock('page-title') ?>
<?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>


<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar"
				style="display: none">
				<div class="card">
					<form action="<?php echo base_url(); ?>/summary/product_search/"
						method="post" accept-charset="utf-8" id="productsummary_search"
						name="productsummary_search">
						<ul id="floating-label" class="px-0 pb-4">
							<h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Search"); ?></h3>
							<div class="col-md-12">
								<div class="row">
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0">
										<?php echo gettext("From Date"); ?>
										</label> <input type="text" name="order_date[]"
											value="<?php echo isset($session_info['order_date'][0]) ? $session_info['order_date'][0] : date("Y-m-d") . " 00:00:00"; ?>"
											id="from_date" size="20"
											class="col-md-12 form-control form-control-lg" />
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0">
										<?php echo gettext("To Date"); ?>
										</label> <input type="text" name="order_date[]"
											value="<?php echo isset($session_info['order_date'][1]) ? $session_info['order_date'][1] : date("Y-m-d") . " 23:59:59"; ?>"
											id="to_date" size="20"
											class="col-md-12 form-control form-control-lg " />
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"> 
										<?php echo gettext("Account"); ?>
										</label> <select name="order_items#accountid"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'>
											<option value=''>--Select--</option>
												<?php
            if (! empty($accountlist)) {
                foreach ($accountlist as $key => $value) {
                    echo "<optgroup label='" . $key . "'>";
                    foreach ($value as $sub_key => $sub_value) {
                        $selected = null;
                        if (isset($session_info['order_items.accountid']) && $session_info['order_items.accountid'] > 0 && $sub_key == $session_info['order_items.accountid']) {
                            $selected = "selected";
                        }
                        echo "<option value = '" . $sub_key . "' $selected >" . $sub_value . "</option>";
                    }
                    ?>
                                                </optgroup>
												<?
                }
            }
            ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Product"); ?></label>
										<select name="product_id"
											class='col-md-12 form-control form-control-lg selectpicker'
											data-live-search='true'>
											<option value="">Select</option>
                                        	<?php
                                        if (! empty($productlist)) {
                                            foreach ($productlist as $key => $val) {
                                                $selected = null;
                                                if (isset($session_info['product_id']) && isset($session_info['product_id']) && ! empty($session_info['product_id']) && $session_info['product_id'] == $key) {
                                                    $selected = "selected";
                                                }
                                                echo "<option value = '" . $sub_key . "' $selected >" . $sub_value . "</option>";
                                            }
                                        }
                                        ?>
					</select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Category"); ?></label>
										<select name="product_category"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
											<option value=''>--Select--</option>
                                        <?php
                                        if (! empty($categorylist)) {
                                            foreach ($categorylist as $key => $val) {
                                                $selected = null;
                                                if (isset($session_info['product_category']) && isset($session_info['product_category']) && ! empty($session_info['product_category']) && $session_info['product_category'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val ?></option>
										<?php
                                            }
                                        }
                                        ?>
                                    </select>
									</div>
									<div class="col-3 input-group">
										<input type="hidden" name="ajax_search" value="1" />
									</div>
									<div class="col-3 input-group">
										<input type="hidden" name="advance_search" value="1" />
									</div>
								</div>
							</div>
						</ul>
						<ul id="floating-label" class="px-0 pb-4">
							<h3 class="alert-dark p-2"><?php echo gettext("Group By"); ?></h3>
							<div class="col-md-12 mb-4">
								<div class="row">
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #Time"); ?></label>
										<select name="time"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_time)) {
                                            foreach ($groupby_time as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['time']) && ! empty($session_info['time']) && $session_info['time'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>  
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #1"); ?></label>
										<select name="groupby_1"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                        <?php
                                        if (! empty($groupby_field)) {
                                            foreach ($groupby_field as $key => $value) {
                                                $selected = null;
                                                if (isset($session_info['groupby_1']) && ! empty($session_info['groupby_1']) && $session_info['groupby_1'] == $key) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                            }
                                        }
                                        ?>        
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #2"); ?></label>
										<select name="groupby_2"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                            <?php
                                            if (! empty($groupby_field)) {
                                                foreach ($groupby_field as $key => $value) {
                                                    $selected = null;
                                                    if (isset($session_info['groupby_2']) && ! empty($session_info['groupby_2']) && $session_info['groupby_2'] == $key) {
                                                        $selected = "selected";
                                                    }
                                                    ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                                }
                                            }
                                            ?>   
                                    </select>
									</div>
									<div class="col-3 input-group">
										<label class="search_label col-md-12 p-0"><?php echo gettext("Group By #3"); ?></label>
										<select name="groupby_3"
											class='col-md-12 form-control form-control-lg selectpicker'
											style='margin-left: 5px;' data-live-search='true'>
                                            <?php
                                            if (! empty($groupby_field)) {
                                                foreach ($groupby_field as $key => $value) {
                                                    $selected = null;
                                                    if (isset($session_info['groupby_3']) && ! empty($session_info['groupby_3']) && $session_info['groupby_3'] == $key) {
                                                        $selected = "selected";
                                                    }
                                                    ?>
                                                <option
												value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
                                            <?php
                                                }
                                            }
                                            ?>   
                                    </select>
									</div>
								</div>
							</div>

							<div class="col-12 p-4">
								<button name="action" type="reset" id="id_reset" value="cancel"
									class="btn btn-secondary float-right ml-2"><?php echo gettext("Clear"); ?></button>
								<button name="action" type="button"
									id="customersummary_search_btn" value="save"
									class="btn btn-success float-right"><?php echo gettext("Search"); ?></button>

								<div class="col-md-5 float-right">
									<label class="search_label col-md-6 text-right"><?php echo gettext("Display records in"); ?> </label>
									<select name="search_in"
										class='col-md-5 form-control form-control-lg selectpicker'
										data-live-search='true'>
										<?php
        if (! empty($search_report)) {
            foreach ($search_report as $key => $value) {
                $selected = null;
                if (isset($session_info['search_in']) && isset($session_info['search_in']) && ! empty($session_info['search_in']) && $session_info['search_in'] == $key) {

                    $selected = "selected";
                }
                ?>
												<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value ?></option>
											<?php
            }
        }
        ?>       
                                    </select>
								</div>
						
						</ul>
					</form>
				</div>
			</div>
		</div>
	</div>
	</div>
</section>
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 pb-4">
			<table id="productsummary_grid" align="left" style="display: none;"></table>
		</div>
	</div>
</section>




<? endblock() ?>	
<? end_extend() ?> 
