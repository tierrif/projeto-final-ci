<? defined('BASEPATH') or exit('No direct script access allowed');

class EnfermagemModel extends MY_Model {
    public function deleteEnfermagensByConsulta($consultaId) {
        $this->db->where('idConsulta', $consultaId);
        $this->delete();
    }

    public function getTable() {
        return 'enfermagem';
    }
}