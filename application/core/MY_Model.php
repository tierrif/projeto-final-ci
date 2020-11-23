<?php defined('BASEPATH') or exit('No direct script access allowed');

abstract class MY_Model extends CI_Model {
    /*
     * Obter o número de valores total.
     */
    public function getCount() {
        // Retorna o número de registos na base de dados nesta tabela.
        return $this->db->count_all($this->getTable());
    }

    /*
     * Obter todos os valores num
     * array.
     */
    public function getAll($limit, $start) {
        // TODO: Obter limites da configuração.
        $this->db->limit($limit, $start);
        $query = $this->db->get($this->getTable());
        return $query->result_array();
    }

    /*
     * Obter um valor específico
     * pelo ID.
     */
    public function getById($id) {
        $query = $this->db->get_where($this->getTable(), ['id' => $id]);
        return $query->row_array();
    }

    /*
     * Inserir na base de dados um novo
     * registo por array associativo $data.
     */
    public function add($data) {
        $this->db->insert($this->getTable(), $data);
    }

    public function update($data) {
        $this->db->where('id', $data['id']);
        return $this->db->update($this->getTable(), $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete($this->getTable());
    }

    /*
     * Retornar, na classe que herdará esta,
     * o nome da tabela correspondente.
     */
    abstract public function getTable();
}
