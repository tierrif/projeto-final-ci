<?php defined('BASEPATH') or exit('No direct script access allowed');

abstract class MY_Model extends CI_Model {
    private $moradaTable = 'morada';

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
        return $this->db->insert_id();
    }

    public function update($data) {
        $this->db->where('id', $data['id']);
        return $this->db->update($this->getTable(), $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete($this->getTable());
    }

    /////////////////////////////////
    //           Moradas           //
    /////////////////////////////////

    public function getMoradaById($id) {
        // Obter uma morada através de $id.
        $query = $this->db->get_where($this->moradaTable, ['id' => $id]);
        // Retornar o resultado, em formato de array para que seja iterável.
        return $query->row_array();
    }

    public function updateMorada($data) {
        $this->db->where('id', $data['id']);
        return $this->db->update($this->moradaTable, $data);
    }

    public function addMorada($data) {
        $this->db->insert($this->moradaTable, $data);
        return $this->db->insert_id();
    }

    public function getAllWithMorada($limit, $start, $controller = '') {
        // Inicializar array a retornar.
        $toReturn = [];
        // Obter todos os registos.
        $all = $this->getAll($limit, $start);
        // Iterar todos os registos.
        foreach ($all as $one) {
            // Adicionar a chave 'morada' para que seja substituído o ID pelo objeto.
            $one['morada'] = $this->getMoradaById($one['idMorada']);
            // Adicionar URI para os detalhes do registo.
            $one['detalhes_uri'] = base_url($controller . '/details/' . $one['id']);
            // Remover o ID, que deixa de ser necessário.
            unset($one['idMorada']);
            // Adicionar ao array a retornar este registo atualizado.
            $toReturn[] = $one;
        }

        // Retornar.
        return $toReturn;
    }

    public function deleteAlongMorada($id) {
        // Obtém ID da morada
        $idMorada = $this->getById($id)['idMorada'];
        // Elimina da tabela de registos.
        $this->delete($id);
        // Elimina da tabela das moradas.
        $this->db->where('id', $id);
        $this->db->delete($this->moradaTable);
    }

    /*
     * Retornar, na classe que herdará esta,
     * o nome da tabela correspondente.
     */
    abstract public function getTable();
}
