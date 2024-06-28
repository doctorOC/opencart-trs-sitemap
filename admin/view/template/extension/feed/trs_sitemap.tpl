<?php

/**
 * LICENSE CC BY-NC-SA 4.0
 *
 * This source file is subject to the Attribution-NonCommercial-ShareAlike 4.0 International License
 * It is also available through the world-wide-web at this URL:
 * https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode
 *
 * @package    TrS Sitemap
 * @copyright  Copyright (c) 2020 drOC
 * @license    https://creativecommons.org/licenses/by-nc-sa/4.0/
 */ ?>

<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-google-sitemap" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_control; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-google-sitemap" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="trs_sitemap_status" id="input-status" class="form-control">
                <?php if ($trs_sitemap_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_limit; ?></label>
            <div class="col-sm-10">
              <input type="text"
                     name="trs_sitemap_limit"
                     placeholder="50000"
                     class="form-control"
                     value="<?php echo $trs_sitemap_limit; ?>"
                     id="input-limit" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-duration"><?php echo $entry_duration; ?></label>
            <div class="col-sm-10">
              <input type="text"
                     name="trs_sitemap_duration"
                     placeholder="24"
                     class="form-control"
                     value="<?php echo $trs_sitemap_duration; ?>"
                     id="input-duration"
                     onkeyup="$('#data-duration').load('index.php?route=extension/feed/trs_sitemap/estimateDuration&token=<?php echo $data_token; ?>&duration=' + $(this).val())" />
                     <p>
                       <pre id="data-duration"><?php echo $data_duration; ?></pre>
                     </p>
             </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_crontab_task; ?></label>
            <div class="col-sm-10">
              <pre><?php echo $data_crontab_task; ?></pre>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_sitemap_url; ?></label>
            <div class="col-sm-10">
              <pre><?php echo $data_sitemap_url; ?></pre>
            </div>
          </div>
        </form>
        <div class="text-center"><?php echo $text_copy; ?></div>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
