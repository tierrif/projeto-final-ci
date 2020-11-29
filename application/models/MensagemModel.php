<? defined('BASEPATH') or exit('No direct script access allowed');

class MensagemModel extends MY_Model {
    public function getAllWithDetails() {
        // Ordenar as mensagens por ver primeiro.
        $this->db->order_by('vista', 'ASC');
        $all = $this->getAll();
        foreach ($all as &$one) {
            $one['vista'] = $one['vista'] ? '<span class="green">sim</span>' : '<span class="red">não</span>';
            $one['detalhes_uri'] = base_url('Mensagens/view/' . $one['id']);
        }

        return $all;
    }

    public function getAmountOfUnreadMessages() {
        $this->db->where('vista', 0);
        $count = count($this->getAll());
        if ($count == 0) return null;
        else return '<span class="red">&nbsp;(' . $count . ')</span>';
    }

    public function getTable() {
        return 'contacto';
    }
}