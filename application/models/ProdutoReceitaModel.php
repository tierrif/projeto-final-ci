<? defined('BASEPATH') or exit('No direct script access allowed');

class ProdutoReceitaModel extends MY_Model {
    public function deleteProdutosReceitasByReceita($receitaId) {
        $this->db->where('idReceita', $receitaId);
        $this->delete();
    }

    public function getTable() {
        return 'produtoreceita';
    }
}