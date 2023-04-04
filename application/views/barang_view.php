<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRUD BARANG</title>
    <link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css')?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/datatables/css/dataTables.bootstrap.min.css')?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')?>" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <h3>Data Barang</h3>
        <br />
        <button class="btn btn-success" onclick="add_barang()">
        <i class="glyphicon glyphicon-plus"></i> Tambah Barang
      </button>
        <button class="btn btn-default" onclick="reload_table()">
        <i class="glyphicon glyphicon-refresh"></i> Reload
      </button>
        <br />
        <br />
        <table id="table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th style="text-align: center">Nama Barang</th>
                    <th style="text-align: center">Harga Beli</th>
                    <th style="text-align: center">Harga Jual</th>
                    <th style="text-align: center">Stok</th>
                    <th style="text-align: center">Foto Barang</th>
                    <th style="width: 150px; text-align: center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th style="text-align: center">Nama Barang</th>
                    <th style="text-align: center">Harga Beli</th>
                    <th style="text-align: center">Harga Jual</th>
                    <th style="text-align: center">Stok</th>
                    <th style="text-align: center">Foto Barang</th>
                    <th style="width: 150px; text-align: center">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <script src="<?php echo base_url('assets/jquery/jquery-2.1.4.min.js')?>"></script>
    <script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js')?>"></script>
    <script src="<?php echo base_url('assets/datatables/js/jquery.dataTables.min.js')?>"></script>
    <script src="<?php echo base_url('assets/datatables/js/dataTables.bootstrap.min.js')?>"></script>

    <script type="text/javascript">
        var save_method; //for save method string
        var table;
        var base_url = "<?php echo base_url();?>";

        $(document).ready(function() {
            //datatables
            table = $("#table").DataTable({
                processing: true, //Feature control the processing indicator.
                serverSide: true, //Feature control DataTables' server-side processing mode.
                order: [], //Initial no order.

                // Load data for the table's content from an Ajax source
                ajax: {
                    url: "<?php echo site_url('barang/ajax_list')?>",
                    type: "POST",
                },

                //Set column definition initialisation properties.
                columnDefs: [{
                    targets: [-1], //last column
                    orderable: false, //set not orderable
                }, {
                    targets: [-2], //2 last column (fotobarang)
                    orderable: false, //set not orderable
                }, ],
            });

            $("input").change(function() {
                $(this).parent().parent().removeClass("has-error");
                $(this).next().empty();
            });
        });

        function add_barang() {
            save_method = "add";
            $("#form")[0].reset(); // reset form on modals
            $(".form-group").removeClass("has-error"); // clear error class
            $(".help-block").empty(); // clear error string
            $("#modal_form").modal("show"); // show bootstrap modal
            $(".modal-title").text("Add barang"); // Set Title to Bootstrap modal title

            $("#fotobarang-preview").hide(); // hide fotobarang preview modal

            $("#label-fotobarang").text("Upload Foto Barang"); // label fotobarang upload
        }

        function edit_barang(id) {
            save_method = "update";
            $("#form")[0].reset(); // reset form on modals
            $(".form-group").removeClass("has-error"); // clear error class
            $(".help-block").empty(); // clear error string

            //Ajax Load data from ajax
            $.ajax({
                url: "<?php echo site_url('barang/ajax_edit')?>/" + id,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('[name="id"]').val(data.id);
                    $('[name="namabarang"]').val(data.namabarang);
                    $('[name="hargabeli"]').val(data.hargabeli);
                    $('[name="hargajual"]').val(data.hargajual);
                    $('[name="stok"]').val(data.stok);
                    $("#modal_form").modal("show"); // show bootstrap modal when complete loaded
                    $(".modal-title").text("Edit barang"); // Set title to Bootstrap modal title

                    $("#fotobarang-preview").show(); // show fotobarang preview modal

                    if (data.fotobarang) {
                        $("#label-fotobarang").text("Ganti Foto Barang"); // label fotobarang upload
                        $("#fotobarang-preview div").html(
                            '<img src="' +
                            base_url +
                            "upload/" +
                            data.fotobarang +
                            '" class="img-responsive">'
                        ); // show fotobarang
                        $("#fotobarang-preview div").append(
                            '<input type="checkbox" name="remove_fotobarang" value="' +
                            data.fotobarang +
                            '"/> Hapus Foto Barang'
                        ); // remove fotobarang
                    } else {
                        $("#label-fotobarang").text("Upload fotobarang"); // label fotobarang upload
                        $("#fotobarang-preview div").text("(Foto Barang Tidak Ada)");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error get data from ajax");
                },
            });
        }

        function reload_table() {
            table.ajax.reload(null, false); //reload datatable ajax
        }

        function save() {
            $("#btnSave").text("saving..."); //change button text
            $("#btnSave").attr("disabled", true); //set button disable
            var url;

            if (save_method == "add") {
                url = "<?php echo site_url('barang/ajax_add')?>";
            } else {
                url = "<?php echo site_url('barang/ajax_update')?>";
            }

            // ajax adding data to database

            var formData = new FormData($("#form")[0]);
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "JSON",
                success: function(data) {
                    if (data.status) {
                        //if success close modal and reload ajax table
                        $("#modal_form").modal("hide");
                        reload_table();
                    } else {
                        for (var i = 0; i < data.inputerror.length; i++) {
                            $('[name="' + data.inputerror[i] + '"]')
                                .parent()
                                .parent()
                                .addClass("has-error"); //select parent twice to select div form-group class and add has-error class
                            $('[name="' + data.inputerror[i] + '"]')
                                .next()
                                .text(data.error_string[i]); //select span help-block class set text error string
                        }
                    }
                    $("#btnSave").text("save"); //change button text
                    $("#btnSave").attr("disabled", false); //set button enable
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error adding / update data");
                    $("#btnSave").text("save"); //change button text
                    $("#btnSave").attr("disabled", false); //set button enable
                },
            });
        }

        function delete_barang(id) {
            if (confirm("Are you sure delete this data?")) {
                // ajax delete data to database
                $.ajax({
                    url: "<?php echo site_url('barang/ajax_delete')?>/" + id,
                    type: "POST",
                    dataType: "JSON",
                    success: function(data) {
                        //if success reload ajax table
                        $("#modal_form").modal("hide");
                        reload_table();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("Error deleting data");
                    },
                });
            }
        }
    </script>

    <!-- Bootstrap modal -->
    <div class="modal fade" id="modal_form" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
                    <h3 class="modal-title">barang Form</h3>
                </div>
                <div class="modal-body form">
                    <form action="#" id="form" class="form-horizontal">
                        <input type="hidden" value="" name="id" />
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3">Nama Barang</label>
                                <div class="col-md-9">
                                    <input name="namabarang" placeholder="Nama Barang" class="form-control" type="text" />
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Harga Beli</label>
                                <div class="col-md-9">
                                    <input name="hargabeli" placeholder="Harga Beli" class="form-control" type="number" />
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Harga Jual</label>
                                <div class="col-md-9">
                                    <input name="hargajual" placeholder="Harga Jual" class="form-control" type="number" />
                                    <span class="help-block"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">Stok</label>
                                <div class="col-md-9">
                                    <input name="stok" placeholder="stok" class="form-control" type="number" />
                                    <span class="help-block"></span>
                                </div>
                            </div>

                            <div class="form-group" id="fotobarang-preview">
                                <label class="control-label col-md-3">Foto Barang</label>
                                <div class="col-md-9">
                                    (Foto Barang Tidak Ada)
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3" id="label-fotobarang">Upload Foto Barang
                  </label>
                                <div class="col-md-9">
                                    <input name="fotobarang" type="file" />
                                    <span class="help-block"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">
              Save
            </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
              Cancel
            </button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    <!-- End Bootstrap modal -->
</body>

</html>