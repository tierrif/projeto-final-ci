<? defined('BASEPATH') or exit('No direct script access allowed');

class UtenteModel extends MY_Model {
    private $moradaTable = 'morada';

    public function getTable() {
        return 'utente';
    }

    public function getMoradaById($id) {
        $query = $this->db->get_where($this->moradaTable, ['id' => $id]);
        return $query->row_array();
    }

    public function getAllWithMorada($limit, $start) {
        $toReturn = [];
        $all = $this->getAll($limit, $start);
        foreach ($all as $one) {
            $one['morada'] = $this->getMoradaById($one['idMorada']);
            unset($one['idMorada']);
            $toReturn[] = $one;
        }
        return $toReturn;
    }
}