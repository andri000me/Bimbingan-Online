<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Bimbingan_model extends MY_Model
{
    protected $column_order = array(null, 'PenelitianID', 'NIM', 'NPP', 'Jenis', 'Judul', 'Status', 'Info','TahunAkademikID', 'created_at', 'updated_at');
    protected $column_search = array('PenelitianID', 'mahasiswa.NIM', 'dosen.NPP','Jenis' ,'Judul','tahun_akademik.TahunAkademik', 'penelitian.Status');
    protected $order = array('PenelitianID' => 'asc');

    public function __construct()
    {
        $this->table       = 'penelitian';
        $this->primary_key = 'PenelitianID';
        $this->fillable    = $this->column_order;
        $this->timestamps  = TRUE;

        $this->has_one['mahasiswa'] = array('Mahasiswa_model', 'NIM', 'NIM');
        $this->has_one['dosen'] = array('Dosen_model', 'NPP', 'NPP');
        $this->has_one['tahun_akademik'] = array('Tahunakademik_model', 'TahunAkademikID', 'TahunAkademikID');
        
        parent::__construct();
    }

    private function _get_datatables_query()
    {

        $this->db->select($this->column_search);

        $this->db->from($this->table);
        $this->db->join('mahasiswa', 'mahasiswa.NIM=penelitian.NIM');
        $this->db->join('dosen', 'dosen.NPP=penelitian.NPP');
        $this->db->join('tahun_akademik', 'tahun_akademik.TahunAkademikID=penelitian.TahunAkademikID');
        $this->db->where('dosen.NPP', $this->session->userdata('username'));
        $this->db->where('tahun_akademik.Status', 1);
        if ($this->input->post('tahun')) {
            $this->db->where('tahun_akademik.TahunAkademikID', $this->input->post('tahun'));
        }

        $i = 0;

        if (!empty($_POST['search']['value'])) {
            foreach ($this->column_search as $item) {
                if ($_POST['search']['value']) {

                    if ($i === 0) {
                        $this->db->group_start();
                        $this->db->like($item, $_POST['search']['value']);
                    } else {
                        $this->db->or_like($item, $_POST['search']['value']);
                    }

                    if (count($this->column_search) - 1 == $i)
                        $this->db->group_end();
                }
                $i++;
            }
        }

        $where = array();
        if (isset($_POST['columns'])) {
            $i = 0;
            foreach ($this->column_search as $item) {
                if (isset($_POST['columns'][$i]) && $_POST['columns'][$i]['search']['value'] != '') {
                    $where[$item] = $_POST['columns'][$i]['search']['value'];
                }
                $i++;
            }

            if (count($where) > 0) {
                $this->db->group_start();
                $this->db->like($where);
                $this->db->group_end();
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();

        $length = isset($_POST['length']) ? $_POST['length'] : 0;
        if ($length != -1) {
            $start  = isset($_POST['start']) ? $_POST['start'] : 0;
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    function not($nim)
    {
        $this->db->select('*');
        $this->db->from('penelitian');
        $this->db->where('NPP', $this->session->userdata('username'));
        $this->db->where_not_in('NIM', $nim);
        $query = $this->db->get();
        return $query->result();
    }

}
/* End of file '/Products_model.php' */
/* Location: ./application/models/Products_model.php */
