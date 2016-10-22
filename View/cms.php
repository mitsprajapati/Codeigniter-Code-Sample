<!-- BEGIN PAGE LEVEL PLUGINS -->
<link href="<?php echo base_url();?>assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>assets/global/css/plugins-md.min.css" rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL PLUGINS -->

<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE HEAD-->
        <div class="page-head">
            <!-- BEGIN PAGE TITLE -->
            <div class="page-title">
                <h1>CMS Pages
                </h1>
            </div>
            <!-- END PAGE TITLE -->
        </div>
        <!-- END PAGE HEAD-->
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="<?php echo base_url('naredcoadmin');?>">Home</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="<?php echo base_url('naredcoadmin/cms');?>">CMS</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span class="active">List CMS Pages</span>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <?php if($this->session->flashdata('error_message')){
            echo '<div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> '.$this->session->flashdata('error_message').' </span>
                    </div>';
        } ?>
        <?php if($this->session->flashdata('success_message')){
            echo '<div class="alert alert-success">
                        <button class="close" data-close="alert"></button>
                        <span> '.$this->session->flashdata('success_message').' </span>
                    </div>';
        } ?>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light portlet-fit portlet-datatable bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class=" icon-layers font-green"></i>
                            <span class="caption-subject font-green sbold uppercase">Cms Pages List</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <br />
                        <a class="btn btn-success" href="<?php echo base_url('naredcoadmin/cms/create')?>"><i class="glyphicon glyphicon-plus"></i> Add CMS Page</a> 
                        <button class="btn btn-danger" onclick="reload_table()"><i class="glyphicon glyphicon-refresh"></i> Reload</button>
                        <br />
                        <br />
                        <table class="table table-striped table-bordered table-hover order-column" id="sample_2">
                            <thead>
                                <tr>
                                    <th> Page Name </th>
                                    <th> Identifier </th>
                                    <th> Date Created </th>
                                    <th> Status </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
</div>



<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo base_url();?>assets/global/scripts/datatable.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->


<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script type="text/javascript">
    var table = $('#sample_2');

    table.dataTable({

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.

        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?php echo site_url('naredcoadmin/cms/cms_list')?>",
            "type": "POST"
        },        
        "pagingType": "bootstrap_full_number",
        //Set column definition initialisation properties.
        "columnDefs": [
            { 
                "targets": [ -1 ], //last column
                "orderable": false, //set not orderable
            },
        ],

    });

    var tableWrapper = jQuery('#sample_2_wrapper');

    table.find('.group-checkable').change(function () {
        var set = jQuery(this).attr("data-set");
        var checked = jQuery(this).is(":checked");
        jQuery(set).each(function () {
            if (checked) {
                $(this).prop("checked", true);
            } else {
                $(this).prop("checked", false);
            }
        });
    });


    //set input/textarea/select event when change value, remove class error and remove text help block 
    $("input").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });
    $("textarea").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });
    $("select").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });


    function reload_table()
    {
        $('#sample_2').dataTable().api().ajax.reload();
    }

    function delete_cms(id)
    {
        if(confirm('Are you sure delete this CMS Page?'))
        {
            // ajax delete data to database
            $.ajax({
                url : "<?php echo site_url('naredcoadmin/cms/delete')?>/"+id,
                type: "POST",
                dataType: "JSON",
                success: function(data)
                {
                    //if success reload ajax table
                    $('#modal_form').modal('hide');
                    reload_table();
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    alert('Error deleting data');
                }
            });

        }
    }

</script>
<!-- END PAGE LEVEL SCRIPTS -->