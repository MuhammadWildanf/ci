<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barang extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Barang_model','barang');
	}

	public function index()
	{
		$this->load->helper('url');
		$this->load->view('barang_view');
	}

	public function ajax_list()
	{
		$this->load->helper('url');

		$list = $this->barang->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $barang) {
			$no++;
			$row = array();
			$row[] = $barang->namabarang;
			$row[] = $barang->hargabeli;
			$row[] = $barang->hargajual;
			$row[] = $barang->stok;
			if($barang->fotobarang)
				$row[] = '<a href="'.base_url('upload/'.$barang->fotobarang).'" target="_blank"><img src="'.base_url('upload/'.$barang->fotobarang).'" class="img-responsive" /></a>';
			else
				$row[] = '(No photo)';

			//add html for action
			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_barang('."'".$barang->id."'".')"><i class="glyphicon glyphicon-pencil"></i> Edit</a>
				  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_barang('."'".$barang->id."'".')"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
		
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->barang->count_all(),
						"recordsFiltered" => $this->barang->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function ajax_edit($id)
	{
		$data = $this->barang->get_by_id($id);
		echo json_encode($data);
	}

	public function ajax_add()
	{
		$this->_validate();
		
		$data = array(
				'namabarang' => $this->input->post('namabarang'),
				'hargabeli' => $this->input->post('hargabeli'),
				'hargajual' => $this->input->post('hargajual'),
				'stok' => $this->input->post('stok'),
			);

		if(!empty($_FILES['fotobarang']['name']))
		{
			$upload = $this->_do_upload();
			$data['fotobarang'] = $upload;
		}

		$insert = $this->barang->save($data);

		echo json_encode(array("status" => TRUE));
	}

	public function ajax_update()
	{
		$this->_validate();
		$data = array(
				'namabarang' => $this->input->post('namabarang'),
				'hargabeli' => $this->input->post('hargabeli'),
				'hargajual' => $this->input->post('hargajual'),
				'stok' => $this->input->post('stok'),
			);

		if($this->input->post('remove_fotobarang')) // if remove photo checked
		{
			if(file_exists('upload/'.$this->input->post('remove_fotobarang')) && $this->input->post('remove_fotobarang'))
				unlink('upload/'.$this->input->post('remove_fotobarang'));
			$data['fotobarang'] = '';
		}

		if(!empty($_FILES['fotobarang']['name']))
		{
			$upload = $this->_do_upload();
			
			//delete file
			$barang = $this->barang->get_by_id($this->input->post('id'));
			if(file_exists('upload/'.$barang->fotobarang) && $barang->fotobarang)
				unlink('upload/'.$barang->fotobarang);

			$data['fotobarang'] = $upload;
		}

		$this->barang->update(array('id' => $this->input->post('id')), $data);
		echo json_encode(array("status" => TRUE));
	}

	public function ajax_delete($id)
	{
		//delete file
		$barang = $this->barang->get_by_id($id);
		if(file_exists('upload/'.$barang->fotobarang) && $barang->fotobarang)
			unlink('upload/'.$barang->fotobarang);
		
		$this->barang->delete_by_id($id);
		echo json_encode(array("status" => TRUE));
	}

	private function _do_upload()
	{
		$config['upload_path']          = 'upload/';
        $config['allowed_types']        = 'jpg|png';
        $config['max_size']             = 100; //set max size allowed in Kilobyte
        $config['file_name']            = round(microtime(true) * 1000); //just milisecond timestamp fot unique name

        $this->load->library('upload', $config);

        if(!$this->upload->do_upload('fotobarang')) //upload and validate
        {
            $data['inputerror'][] = 'fotobarang';
			$data['error_string'][] = 'Upload error: '.$this->upload->display_errors('',''); //show ajax error
			$data['status'] = FALSE;
			echo json_encode($data);
			exit();
		}
		return $this->upload->data('file_name');
	}

	private function _validate()
	{
		$data = array();
		$data['error_string'] = array();
		$data['inputerror'] = array();
		$data['status'] = TRUE;

		

		if($this->input->post('namabarang') == '')
		{
			$data['inputerror'][] = 'namabarang';
			$data['error_string'][] = 'Nama Barang Wajib Di Isi';
			$data['status'] = FALSE;
		}

		if($this->input->post('hargabeli') == '')
		{
			$data['inputerror'][] = 'hargabeli';
			$data['error_string'][] = 'Harga Beli Wajib Di Isi';
			$data['status'] = FALSE;
		}


		if($this->input->post('hargajual') == '')
		{
			$data['inputerror'][] = 'hargajual';
			$data['error_string'][] = 'Harga Jual Wajib Di Isi';
			$data['status'] = FALSE;
		}

		if($this->input->post('stok') == '')
		{
			$data['inputerror'][] = 'stok';
			$data['error_string'][] = 'Stok Wajib Di Isi';
			$data['status'] = FALSE;
		}

		if($data['status'] === FALSE)
		{
			echo json_encode($data);
			exit();
		}
	}

}
