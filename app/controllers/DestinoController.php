<?php

namespace App\Controllers;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/GeocodingService.php';
use App\Utils\GeocodingService;

use Config\Database;
use PDO;
use PDOException;
use Exception;

class DestinoController {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();

            if (!$this->db) {
                throw new Exception("Falha ao conectar com o banco de dados.");
            }
        } catch (Exception $e) {
            $this->handleError("Erro ao inicializar o controlador de destinos.", $e);
        }
    }

    public function obterCoordenadas($destinoId) {
        try {
            // Corrigida a query com os nomes corretos das colunas
            $query = "SELECT dt.id, dt.nome_destino, l.nome_local, 
                         p.nome_provincia,
                         l.latitude, l.longitude, l.id_localizacao
                  FROM destinos_turisticos dt
                  LEFT JOIN localizacoes l ON dt.id_localizacao = l.id_localizacao
                  LEFT JOIN provincias p ON l.id_provincia = p.id_provincia
                  WHERE dt.id = ?";
                  
            $stmt = $this->db->prepare($query);
            $stmt->execute([$destinoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Logar para debug
            error_log("Coordenadas obtidas: " . json_encode($result));
            
            return $result ?: null;
        } catch (Exception $e) {
            $this->handleError("Erro ao obter coordenadas do destino.", $e);
            return null;
        }
    }
    
    public function buscarEArmazenarCoordenadas($destinoId) {
        try {
            // Obtém os dados do destino
            $destino = $this->obterCoordenadas($destinoId);
            
            if (!$destino) {
                error_log("Destino ID $destinoId não encontrado");
                return null;
            }
            
            // Verificar se já tem coordenadas válidas (diferente de NULL ou 0)
            if ($destino && 
                isset($destino['latitude']) && isset($destino['longitude']) && 
                $destino['latitude'] != 0 && $destino['longitude'] != 0) {
                
                error_log("Retornando coordenadas existentes: " . $destino['latitude'] . ", " . $destino['longitude']);
                return [
                    'latitude' => (float)$destino['latitude'],
                    'longitude' => (float)$destino['longitude']
                ];
            }
            
            // Se não tiver coordenadas válidas, buscar na API
            try {
                $geocoder = new GeocodingService();
                $endereco = '';
                
                // Construir endereço com informações disponíveis
                if (!empty($destino['nome_local'])) {
                    $endereco .= $destino['nome_local'];
                }
                
                if (!empty($destino['nome_provincia'])) {
                    $endereco .= (!empty($endereco) ? ', ' : '') . $destino['nome_provincia'];
                }
                
                // Adicionar nome do destino se for útil
                $endereco .= (!empty($endereco) ? ', ' : '') . $destino['nome_destino'] . ', Angola';
                
                error_log("Buscando coordenadas para: $endereco");
                $coordenadas = $geocoder->buscarCoordenadas($endereco);
                
                if (!$coordenadas && !empty($destino['nome_local'])) {
                    // Tenta novamente apenas com a localidade
                    error_log("Tentando novamente apenas com localidade: " . $destino['nome_local'] . ", Angola");
                    $coordenadas = $geocoder->buscarCoordenadas($destino['nome_local'] . ", Angola");
                }
                
                if ($coordenadas) {
                    // Garantir que são números
                    $lat = (float)$coordenadas['latitude'];
                    $lng = (float)$coordenadas['longitude'];
                    
                    error_log("Coordenadas encontradas: $lat, $lng");
                    
                    // Armazena no banco de dados
                    $idLocalizacao = $destino['id_localizacao'];
                    
                    if ($idLocalizacao) {
                        // Atualiza localização existente
                        $query = "UPDATE localizacoes 
                                 SET latitude = ?, longitude = ? 
                                 WHERE id_localizacao = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([$lat, $lng, $idLocalizacao]);
                        error_log("Localização ID $idLocalizacao atualizada com coordenadas");
                    } else {
                        // Cria nova localização
                        $nome_local = $destino['nome_local'] ?? $destino['nome_destino'];
                        $query = "INSERT INTO localizacoes (nome_local, latitude, longitude) 
                                 VALUES (?, ?, ?)";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([$nome_local, $lat, $lng]);
                        
                        $idLocalizacao = $this->db->lastInsertId();
                        error_log("Nova localização criada com ID $idLocalizacao");
                        
                        // Atualiza o destino com o id da localização
                        $query = "UPDATE destinos_turisticos SET id_localizacao = ? WHERE id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([$idLocalizacao, $destinoId]);
                        error_log("Destino atualizado com nova localização");
                    }
                    
                    return [
                        'latitude' => $lat,
                        'longitude' => $lng
                    ];
                } else {
                    error_log("Não foi possível encontrar coordenadas na API");
                    return null;
                }
            } catch (Exception $e) {
                error_log("Erro ao usar o GeocodingService: " . $e->getMessage());
                
                // Se falhar a API, retornar coordenadas padrão para Angola (temporário)
                return [
                    'latitude' => -8.838333,  // Coordenadas de Luanda
                    'longitude' => 13.234444
                ];
            }
        } catch (Exception $e) {
            $this->handleError("Erro ao buscar e armazenar coordenadas.", $e);
            error_log("Exception: " . $e->getMessage());
            return null;
        }
    }



    
    public function cadastrarDestino($nome, $descricao, $idLocalizacao, $imagem, $idCategoria = null, $isMaravilha = 0) {
        try {
            if (empty($nome) || empty($descricao) || empty($idLocalizacao)) {
                throw new Exception("Os campos nome, descrição e localização devem ser preenchidos.");
            }

            $sqlCheck = "SELECT COUNT(*) FROM destinos_turisticos WHERE nome_destino = :nome";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':nome', $nome);
            $stmtCheck->execute();

            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Já existe um destino com este nome.");
            }

            $uploadDir = __DIR__ . '/../../uploads/';
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $uploadedFile = null;

            if (is_array($imagem) && isset($imagem['tmp_name']) && $imagem['tmp_name']) {
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception("Tipo de arquivo inválido. Somente imagens (jpg, jpeg, png, gif) são permitidas.");
                }

                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Erro ao criar diretório de uploads.");
                }

                $uniqueFileName = uniqid() . '.' . $extension;
                $uploadedFile = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($imagem['tmp_name'], $uploadedFile)) {
                    throw new Exception("Erro ao mover o arquivo para o diretório de uploads.");
                }

                $uploadedFile = $uniqueFileName;
            }

            $sql = "INSERT INTO destinos_turisticos (nome_destino, descricao, imagem, id_localizacao, id_categoria, is_maravilha) 
                    VALUES (:nome, :descricao, :imagem, :id_localizacao, :id_categoria, :is_maravilha)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':imagem', $uploadedFile);
            $stmt->bindParam(':id_localizacao', $idLocalizacao, PDO::PARAM_INT);
            $stmt->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
            $stmt->bindParam(':is_maravilha', $isMaravilha, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar a query: " . implode(", ", $stmt->errorInfo()));
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao cadastrar destino.", $e);
            return false;
        }
    }

    public function listarDestinos() {
        try {
            $sql = "SELECT dt.*, l.nome_local, l.latitude, l.longitude 
                    FROM destinos_turisticos dt
                    LEFT JOIN localizacoes l ON dt.id_localizacao = l.id_localizacao
                    ORDER BY dt.nome_destino ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar destinos.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar destinos.", $e);
            return [];
        }
    }

    public function listarCategorias() {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nome_categoria ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar categorias.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar categorias.", $e);
            return [];
        }
    }

    // Método único para obter detalhes de um destino específico
    public function obterDetalhes($id) {
        try {
            $query = "SELECT dt.id, dt.nome_destino, dt.descricao, dt.imagem, dt.is_maravilha,
                             dt.id_categoria, l.nome_local, l.latitude, l.longitude
                      FROM destinos_turisticos dt 
                      LEFT JOIN localizacoes l ON dt.id_localizacao = l.id_localizacao
                      WHERE dt.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter detalhes do destino: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarDestino($id, $nome, $descricao, $idLocalizacao, $imagem = null, $idCategoria = null, $isMaravilha = 0) {
        try {
            if (empty($nome) || empty($descricao) || empty($idLocalizacao)) {
                throw new Exception("Os campos nome, descrição e localização devem ser preenchidos.");
            }

            $sql = "UPDATE destinos_turisticos 
                    SET nome_destino = :nome, 
                        descricao = :descricao, 
                        id_localizacao = :id_localizacao,
                        id_categoria = :id_categoria,
                        is_maravilha = :is_maravilha";

            $params = [
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':id_localizacao' => $idLocalizacao,
                ':id_categoria' => $idCategoria,
                ':is_maravilha' => $isMaravilha,
                ':id' => $id
            ];

            if ($imagem && is_array($imagem) && !empty($imagem['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../uploads/';
                $extension = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception("Tipo de arquivo inválido.");
                }

                $uniqueFileName = uniqid() . '.' . $extension;
                $uploadedFile = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($imagem['tmp_name'], $uploadedFile)) {
                    throw new Exception("Erro ao mover o arquivo para o diretório de uploads.");
                }

                $sql .= ", imagem = :imagem";
                $params[':imagem'] = $uniqueFileName;
            }

            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao atualizar destino.", $e);
            return false;
        }
    }

    public function excluirDestino($id) {
        try {
            $sql = "DELETE FROM destinos_turisticos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao excluir destino.");
            }

            return true;
        } catch (Exception $e) {
            $this->handleError("Erro ao excluir destino.", $e);
            return false;
        }
    }

    public function buscarAvaliacoesPorDestino($idDestino) {
        try {
            $sql = "SELECT a.*, u.nome as nome_usuario 
                    FROM avaliacoes a
                    JOIN usuarios u ON a.id_usuario = u.id_usuario
                    WHERE a.id_destino = :id_destino
                    ORDER BY a.data_avaliacao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao buscar avaliações do destino.", $e);
            return [];
        }
    }

    public function obterImagensDestino($idDestino) {
        try {
            $sql = "SELECT caminho_imagem AS caminho FROM imagens WHERE id_destino = :id_destino";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao obter imagens do destino.", $e);
            return [];
        }
    }

    public function calcularMediaAvaliacoes($idDestino) {
        try {
            $sql = "SELECT ROUND(AVG(nota), 1) as media_nota 
                    FROM avaliacoes 
                    WHERE id_destino = :id_destino";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['media_nota'] ?? 0;
        } catch (Exception $e) {
            $this->handleError("Erro ao calcular média de avaliações.", $e);
            return 0;
        }
    }

    public function listarLocalizacoes() {
        try {
            $sql = "SELECT id_localizacao, nome_local FROM localizacoes ORDER BY nome_local ASC";
            $stmt = $this->db->query($sql);

            if (!$stmt) {
                throw new Exception("Erro ao executar a consulta para listar localizações.");
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->handleError("Erro ao listar localizações.", $e);
            return [];
        }
    }

    private function handleError($message, Exception $e) {
        $fullMessage = $message . " Detalhes: " . $e->getMessage();
        echo $fullMessage;
        error_log($fullMessage);
        return false; // Substituído o die() por return false para não interromper a execução
    }
}