<?php
namespace App\Utils;

class GeocodingService {
    private $baseUrl = 'https://nominatim.openstreetmap.org/search';
    
    /**
     * Busca coordenadas para um local usando Nominatim
     * 
     * @param string $endereco Nome do local ou endereço
     * @param string $pais País para limitar a busca (opcional)
     * @return array|null Array com latitude e longitude ou null se não encontrado
     */
    public function buscarCoordenadas($endereco, $pais = 'Angola') {
        // Adiciona o país à consulta para melhorar precisão
        $consulta = $endereco;
        if ($pais) {
            $consulta .= ", $pais";
        }
        
        // Prepara a URL
        $params = [
            'q' => $consulta,
            'format' => 'json',
            'limit' => 1,
        ];
        
        $url = $this->baseUrl . '?' . http_build_query($params);
        
        // Adiciona um user-agent para respeitar as políticas do Nominatim
        $options = [
            'http' => [
                'header' => "User-Agent: TurismoAngola/1.0\r\n",
                'timeout' => 5,
            ]
        ];
        
        $context = stream_context_create($options);
        
        // Faz a requisição
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        // Verifica se encontrou algum resultado
        if (empty($data)) {
            return null;
        }
        
        // Retorna as coordenadas do primeiro resultado
        return [
            'latitude' => $data[0]['lat'],
            'longitude' => $data[0]['lon'],
            'display_name' => $data[0]['display_name']
        ];
    }
}