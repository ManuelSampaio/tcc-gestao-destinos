<?php
namespace App\Models;

class CategoriaModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obter todas as categorias
     * 
     * @return array Array com todas as categorias
     */
    public function obterCategorias() {
        try {
            $query = "SELECT id_categoria, nome_categoria, descricao_categoria 
                      FROM categorias 
                      ORDER BY nome_categoria ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Se não conseguir buscar do banco, retorna categorias padrão
            return $this->getDefaultCategorias();
        }
    }
    
    /**
     * Obter uma categoria específica pelo ID
     * 
     * @param int $id ID da categoria
     * @return array|null Dados da categoria ou null se não encontrada
     */
    public function obterCategoriaPorId($id) {
        try {
            $query = "SELECT id_categoria, nome_categoria, descricao_categoria 
                      FROM categorias 
                      WHERE id_categoria = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return null;
        }
    }
    
    /**
     * Inserir uma nova categoria
     * 
     * @param string $nome Nome da categoria
     * @param string $descricao Descrição da categoria
     * @return bool|string True em caso de sucesso, mensagem de erro em caso de falha
     */
    public function inserirCategoria($nome, $descricao = '') {
        try {
            $query = "INSERT INTO categorias (nome_categoria, descricao_categoria) 
                      VALUES (:nome, :descricao)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $nome, \PDO::PARAM_STR);
            $stmt->bindParam(':descricao', $descricao, \PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            return "Erro ao inserir categoria: " . $e->getMessage();
        }
    }
    
    /**
     * Atualizar uma categoria existente
     * 
     * @param int $id ID da categoria
     * @param string $nome Novo nome da categoria
     * @param string $descricao Nova descrição da categoria
     * @return bool|string True em caso de sucesso, mensagem de erro em caso de falha
     */
    public function atualizarCategoria($id, $nome, $descricao = '') {
        try {
            $query = "UPDATE categorias 
                      SET nome_categoria = :nome, 
                          descricao_categoria = :descricao 
                      WHERE id_categoria = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome, \PDO::PARAM_STR);
            $stmt->bindParam(':descricao', $descricao, \PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            return "Erro ao atualizar categoria: " . $e->getMessage();
        }
    }
    
    /**
     * Excluir uma categoria
     * 
     * @param int $id ID da categoria a ser excluída
     * @return bool|string True em caso de sucesso, mensagem de erro em caso de falha
     */
    public function excluirCategoria($id) {
        try {
            // Verificar se há destinos usando esta categoria
            $query = "SELECT COUNT(*) as total FROM destinos WHERE id_categoria = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return "Não é possível excluir esta categoria porque existem destinos associados a ela.";
            }
            
            // Se não houver destinos, prosseguir com a exclusão
            $query = "DELETE FROM categorias WHERE id_categoria = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            return "Erro ao excluir categoria: " . $e->getMessage();
        }
    }
    
    /**
     * Contar quantos destinos usam uma categoria específica
     * 
     * @param int $id ID da categoria
     * @return int Número de destinos usando a categoria
     */
    public function contarDestinosPorCategoria($id) {
        try {
            $query = "SELECT COUNT(*) as total FROM destinos WHERE id_categoria = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
        } catch (\PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Retorna categorias padrão caso não exista no banco de dados
     * Este método é um fallback se a tabela não existir ou ocorrer erro
     * 
     * @return array Array com categorias padrão
     */
    private function getDefaultCategorias() {
        return [
            ['id_categoria' => 1, 'nome_categoria' => 'Praias e Litoral', 'descricao_categoria' => 'Praias e áreas costeiras de Angola'],
            ['id_categoria' => 2, 'nome_categoria' => 'Parques Nacionais e Reservas', 'descricao_categoria' => 'Áreas protegidas e parques naturais'],
            ['id_categoria' => 3, 'nome_categoria' => 'Montanhas e Formações Rochosas', 'descricao_categoria' => 'Áreas montanhosas e formações geológicas interessantes'],
            ['id_categoria' => 4, 'nome_categoria' => 'Patrimônio Histórico-Cultural', 'descricao_categoria' => 'Locais históricos e culturais importantes'],
            ['id_categoria' => 5, 'nome_categoria' => 'Ecoturismo e Natureza', 'descricao_categoria' => 'Destinos para observação da natureza e ecoturismo'],
            ['id_categoria' => 6, 'nome_categoria' => 'Desertos e Savanas', 'descricao_categoria' => 'Paisagens desérticas e savanas angolanas'],
            ['id_categoria' => 7, 'nome_categoria' => 'Turismo Urbano', 'descricao_categoria' => 'Cidades e áreas urbanas interessantes'],
            ['id_categoria' => 8, 'nome_categoria' => 'Turismo Rural e Comunitário', 'descricao_categoria' => 'Experiências em comunidades e áreas rurais'],
            ['id_categoria' => 9, 'nome_categoria' => 'Cultura e Festivais', 'descricao_categoria' => 'Eventos culturais e festivais tradicionais'],
            ['id_categoria' => 10, 'nome_categoria' => 'Turismo Religioso', 'descricao_categoria' => 'Locais de significado religioso e peregrinações'],
            ['id_categoria' => 11, 'nome_categoria' => 'Roteiros Etnográficos', 'descricao_categoria' => 'Roteiros focados nas etnias e culturas tradicionais']
        ];
    }
    
    /**
     * Criar a tabela de categorias se não existir
     * Este método pode ser chamado durante a instalação do sistema
     * 
     * @return bool|string True em caso de sucesso, mensagem de erro em caso de falha
     */
    public function criarTabelaCategorias() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS categorias (
                id_categoria INT AUTO_INCREMENT PRIMARY KEY,
                nome_categoria VARCHAR(100) NOT NULL,
                descricao_categoria TEXT,
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute();
            
            // Se a tabela for criada com sucesso e estiver vazia, insere categorias padrão
            if ($success) {
                $checkQuery = "SELECT COUNT(*) as total FROM categorias";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute();
                $result = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($result['total'] == 0) {
                    // Insere categorias padrão
                    $this->inserirCategoriasPadrao();
                }
            }
            
            return $success;
        } catch (\PDOException $e) {
            return "Erro ao criar tabela de categorias: " . $e->getMessage();
        }
    }
    
    /**
     * Inserir categorias padrão no banco de dados
     * 
     * @return bool Verdadeiro se todas as categorias foram inseridas com sucesso
     */
    private function inserirCategoriasPadrao() {
        $categorias = $this->getDefaultCategorias();
        $success = true;
        
        foreach ($categorias as $categoria) {
            $query = "INSERT INTO categorias (id_categoria, nome_categoria, descricao_categoria) 
                      VALUES (:id, :nome, :descricao)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $categoria['id_categoria'], \PDO::PARAM_INT);
            $stmt->bindParam(':nome', $categoria['nome_categoria'], \PDO::PARAM_STR);
            $stmt->bindParam(':descricao', $categoria['descricao_categoria'], \PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $success = false;
            }
        }
        
        return $success;
    }
}