<style>
.details_table td{
    text-shadow: 0 1px 0 white;
    /*font-weight: bold;*/
    padding: 6px;
    font-size: 11px;
    text-align: center;
/*    background-color: #E6E6E6;*/
    vertical-align:middle;
}
    </style>
<? extend('master.php') ?>
  <? startblock('extra_head') ?>
  <? endblock() ?>      
    <? startblock('page-title') ?>
        <?=$page_title?><br/>
    <? endblock() ?>
	<? startblock('content') ?>
 <section class="slice color-three padding-b-20">
	<div class="w-section inverse no-padding">
    	<div class="container">
        	<div class="row">
                <div class="col-md-12">  
            <fieldset >
                <legend><span style="font-size:15px;padding:5px;font-family:Open sans,sans-serif;color:#163B80; ">Error In CSV File</span></legend><section class="slice color-three padding-b-20">
	<div class="w-section inverse no-padding">
    	<div class="container">
        	<div class="row">
                <div class="col-md-12">      
          
                    Records Imported Successfully: <?= $import_record_count; ?><br/>
                    Records Not Imported : <?= $failure_count?></div>  
            </div>
        </div>
    </div>
</section>
  </div>
        </div>
    </div> 
<br/>
                    <div class="col-md-12 padding-b-10">
                   <div class="pull-right">
                        <a href="<?= base_url().'local_number/local_number_error_download/'?>"><input class="btn btn-line-sky margin-x-10" id="dwnld_err" type="button" name="action" value="Download Errors" /> </a>
                     <a href="<?= base_url().'local_number/local_number_list/'?>"><input class="btn btn-line-parrot" id="local_number_list" type="button" name="action" value="Back to Local Number List" /> </a>  </div></div>
            </fieldset></section>
        <? endblock() ?>
    <? startblock('sidebar') ?>
        Filter by
    <? endblock() ?>
<? end_extend() ?>  
    

