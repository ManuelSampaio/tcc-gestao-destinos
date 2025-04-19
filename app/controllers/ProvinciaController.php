<?php
namespace App\Controllers;

// Use caminho absoluto para encontrar corretamente o arquivo
require_once __DIR__ . '/../models/ProvinciaModel.php';
use App\Models\ProvinciaModel;
use PDO;
use Exception;

class ProvinciaController {
    private $model;

    public function __construct() {
        $this->model = new ProvinciaModel();
    }

    /**
     * Lista todas as províncias
     *
     * @return array|bool Lista de províncias ou false em caso de erro
     */
    public function listarProvincias() {
        try {
            return $this->model->listarTodas();
        } catch (Exception $e) {
            error_log('Erro ao listar províncias: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém uma província pelo ID
     *
     * @param int $id ID da província
     * @return array|bool Dados da província ou false em caso de erro
     */
    public function obterProvinciaPorId($id) {
        try {
            return $this->model->buscarPorId($id);
        } catch (Exception $e) {
            error_log('Erro ao buscar província: ' . $e->getMessage());
            return false;
        }
    }
}