<?php 

namespace App\Controllers;

// Use caminho absoluto para encontrar corretamente o arquivo
require_once __DIR__ . '/../models/LocalizacaoModel.php';
use App\Models\LocalizacaoModel;
use Config\Database; // Adicionado o import correto para Database
use PDO;
use Exception;

class LocalizacaoController {
    private $model;
    
    public function __construct() {
        $this->model = new LocalizacaoModel();
    }
    
    /**
     * Cadastra uma nova localização no sistema
     * 
     * @param string $nomeLocal Nome do local
     * @param string $latitude Latitude (opcional)
     * @param string $longitude Longitude (opcional)
     * @param int $provinciaId ID da província
     * @return int|bool ID da localização inserida ou false em caso de erro
     */
    public function cadastrarLocalizacao($nomeLocal, $latitude, $longitude, $provinciaId) {
        try {
            // Validação básica
            if (empty($nomeLocal) || empty($provinciaId)) {
                throw new Exception("Nome do local e província são obrigatórios");
            }
            
            // Limpa e formata os dados
            $nomeLocal = trim($nomeLocal);
            $latitude = !empty($latitude) ? trim($latitude) : null;
            $longitude = !empty($longitude) ? trim($longitude) : null;
            $provinciaId = (int)$provinciaId;
            
            // Cadastra a localização
            return $this->model->inserir($nomeLocal, $latitude, $longitude, $provinciaId);
        } catch (Exception $e) {
            // Log do erro
            error_log('Erro ao cadastrar localização: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza uma localização existente
     * 
     * @param int $idLocalizacao ID da localização a ser atualizada
     * @param string $nomeLocal Nome do local
     * @param int|null $idProvincia ID da província (opcional)
     * @param float|null $latitude Latitude (opcional)
     * @param float|null $longitude Longitude (opcional)
     * @return bool Sucesso ou falha da operação
     */
    public function atualizarLocalizacao($idLocalizacao, $nomeLocal, $idProvincia = null, $latitude = null, $longitude = null) {
        try {
            // Validação básica
            if (empty($idLocalizacao) || empty($nomeLocal)) {
                throw new Exception("ID da localização e nome do local são obrigatórios");
            }
            
            // Limpa e formata os dados
            $nomeLocal = trim($nomeLocal);
            $latitude = !empty($latitude) ? (float)trim($latitude) : null;
            $longitude = !empty($longitude) ? (float)trim($longitude) : null;
            $idProvincia = !empty($idProvincia) ? (int)$idProvincia : null;
            
            // Atualiza a localização usando o método do model
            return $this->model->atualizar($idLocalizacao, $nomeLocal, $idProvincia, $latitude, $longitude);
        } catch (Exception $e) {
            // Log do erro
            error_log('Erro ao atualizar localização: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém uma localização pelo ID
     *
     * @param int $id ID da localização
     * @return array|bool Dados da localização ou false em caso de erro
     */
    public function obterLocalizacaoPorId($id) {
        try {
            return $this->model->buscarPorId($id);
        } catch (Exception $e) {
            error_log('Erro ao buscar localização: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todas as localizações
     *
     * @return array|bool Lista de localizações ou false em caso de erro
     */
    public function listarLocalizacoes() {
        try {
            return $this->model->listarTodos();
        } catch (Exception $e) {
            error_log('Erro ao listar localizações: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exclui uma localização pelo ID
     *
     * @param int $id ID da localização a ser excluída
     * @return bool True se for excluída com sucesso, False caso contrário
     */
    public function excluirLocalizacao($id) {
        try {
            if (empty($id)) {
                throw new Exception("ID da localização é obrigatório");
            }
            
            return $this->model->excluir($id);
        } catch (Exception $e) {
            error_log('Erro ao excluir localização: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todas as localizações de uma província
     *
     * @param int $provinciaId ID da província
     * @return array|bool Lista de localizações ou false em caso de erro
     */
    public function listarLocalizacoesPorProvincia($provinciaId) {
        try {
            if (empty($provinciaId)) {
                throw new Exception("ID da província é obrigatório");
            }
            
            return $this->model->buscarPorProvincia($provinciaId);
        } catch (Exception $e) {
            error_log('Erro ao listar localizações por província: ' . $e->getMessage());
            return false;
        }
    }
}