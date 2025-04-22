<?php 

namespace App\Controllers;

// Use caminho absoluto para encontrar corretamente o arquivo
require_once __DIR__ . '/../models/LocalizacaoModel.php';
use App\Models\LocalizacaoModel;
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
     * Método alternativo para criar localização que aceita array de dados
     * 
     * @param array|string $dados Array de dados ou nome do local
     * @param string|null $latitude Latitude (opcional, ignorado se $dados for array)
     * @param string|null $longitude Longitude (opcional, ignorado se $dados for array)
     * @param int|null $provinciaId ID da província (ignorado se $dados for array)
     * @return int|bool ID da localização inserida ou false em caso de erro
     */
    public function criarLocalizacao($dados, $latitude = null, $longitude = null, $provinciaId = null) {
        try {
            // Se $dados for um array, extrair os valores dele
            if (is_array($dados)) {
                $nomeLocal = isset($dados['nome_local']) ? $dados['nome_local'] : '';
                $latitude = isset($dados['latitude']) ? $dados['latitude'] : null;
                $longitude = isset($dados['longitude']) ? $dados['longitude'] : null;
                $provinciaId = isset($dados['id_provincia']) ? $dados['id_provincia'] : null;
                
                // Validação básica
                if (empty($nomeLocal) || empty($provinciaId)) {
                    throw new Exception("Nome do local e província são obrigatórios");
                }
                
                return $this->cadastrarLocalizacao($nomeLocal, $latitude, $longitude, $provinciaId);
            } else {
                // $dados é o nome_local
                $nomeLocal = $dados;
                
                // Validação básica
                if (empty($nomeLocal) || empty($provinciaId)) {
                    throw new Exception("Nome do local e província são obrigatórios");
                }
                
                return $this->cadastrarLocalizacao($nomeLocal, $latitude, $longitude, $provinciaId);
            }
        } catch (Exception $e) {
            error_log('Erro ao criar localização: ' . $e->getMessage());
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
     * Atualiza apenas a província de uma localização
     *
     * @param int $idLocalizacao ID da localização
     * @param int $provinciaId ID da nova província
     * @return bool Sucesso ou falha da operação
     */
    public function atualizarProvincia($idLocalizacao, $provinciaId) {
        try {
            if (empty($idLocalizacao)) {
                throw new Exception("ID da localização é obrigatório");
            }
            
            if (empty($provinciaId)) {
                throw new Exception("ID da província é obrigatório");
            }
            
            // Converte para inteiros para garantir tipagem correta
            $idLocalizacao = (int)$idLocalizacao;
            $provinciaId = (int)$provinciaId;
            
            // Usamos o método atualizar do modelo com apenas o campo de província
            return $this->model->atualizar($idLocalizacao, null, $provinciaId);
        } catch (Exception $e) {
            error_log("Erro ao atualizar província da localização: " . $e->getMessage());
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
     * Obtém uma localização pelo nome e ID da província
     *
     * @param int $provinciaId ID da província
     * @param string $nomeLocal Nome do local
     * @return array|bool Dados da localização ou false em caso de erro
     */
    public function obterLocalizacaoPorProvinciaENome($provinciaId, $nomeLocal) {
        try {
            if (empty($provinciaId) || empty($nomeLocal)) {
                throw new Exception("ID da província e nome do local são obrigatórios");
            }
            
            // Limpa e formata os dados
            $nomeLocal = trim($nomeLocal);
            $provinciaId = (int)$provinciaId;
            
            // É necessário implementar este método no modelo (LocalizacaoModel)
            return $this->model->buscarPorProvinciaENome($provinciaId, $nomeLocal);
        } catch (Exception $e) {
            error_log('Erro ao buscar localização por província e nome: ' . $e->getMessage());
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