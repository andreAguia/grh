<?php
class Aposentadoria{
 /**
  * Abriga as várias rotina referentes a aposentadoria do servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }
    
    ###########################################################

    function get_numServidoresAposentados(){

    /**
     * informa o número de Servidores Ativos
     * 
     * @param integer $idPessoa do servidor
     */
        
        $select = 'SELECT idServidor
                     FROM tbservidor
                    WHERE situacao = 2';
        
        $pessoal = new Pessoal();
        $count = $pessoal->count($select);
        return $count;
    }

     ###########################################################

    /**
     * Método get_tempoServicoUenf
     * informa o total de dias corridos de tempo de serviço dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function get_tempoServicoUenf($idServidor){
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        # Data de admissão
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);   # Data de entrada na UENF
        
        # Define a data inicial
        $dtInicial = date_to_bd($dtAdmissao);
        
        # Verifica se o servidor é inativo e pega a data de saída dele
        $dtSaida = $pessoal->get_dtSaida($idServidor);
        
        $dtHoje = date("Y-m-d");
        
        # Define a data término
        if(!vazio($dtSaida)){       // Se tem saída é a saída
            $dtFinal = $dtSaida;
        }else{
            $dtFinal = $dtHoje;     // Se não tiver saída é hoje
        }

        # Calcula a diferença em segundos entre as datas
        $diferenca = strtotime($dtFinal) - strtotime($dtInicial);

        # Calcula a diferença em dias
        $dias = floor($diferenca / (60 * 60 * 24));

        return $dias;
    }

    ###########################################################

    /**
     * Método get_tempoAverbadoPublico
     * informa o total de dias de tempo averbado em empresa Pública
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_tempoAverbadoPublico($idServidor){
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = '.$idServidor.'
                         ORDER BY total';
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        $row = $pessoal->select($select,FALSE);
        
        if(is_null($row[0])){
            return 0;
        }else{
            return $row[0];
        }
    }

    ###########################################################

    /**
     * Método get_tempoAverbadoPrivado
     * informa o total de dias de tempo averbado em empresa privada
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_tempoAverbadoPrivado($idServidor) {
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 2 AND idServidor = '.$idServidor.'
                         ORDER BY total';

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        $row = $pessoal->select($select,FALSE);

        if(is_null($row[0])){
            return 0;
        }else{
            return $row[0];
        }
    }

    ###########################################################

    /**
     * Método get_tempoGeral
     * informa o total geral de dias
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_tempoGeral($idServidor) {
        
        $uenf = $this->get_tempoServicoUenf($idServidor);
        $publico = $this->get_tempoAverbadoPublico($idServidor);
        $privado = $this->get_tempoAverbadoPrivado($idServidor);
        $total = $uenf + $publico + $privado;
        
        return $total;
    }

    ###########################################################

    /**
     * Método get_ocorrencias
     * informa o total de dias de tempo averbado em empresa privada
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_ocorrencias($idServidor) {

        $reducao = "SELECT tbtipolicenca.nome as tipo,
                           SUM(numDias) as dias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = $idServidor
                       AND tbtipolicenca.tempoServico IS TRUE
                  GROUP BY tbtipolicenca.nome";
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        $dados = $pessoal->select($reducao);

        # Somatório
        $totalOcorrencias = array_sum(array_column($dados, 'dias'));
        
        return $totalOcorrencias;
    }

    ###########################################################

    /**
     * Método get_diasFaltando
     * informa o total de dias que faltam para completar o tempo de serviço para aposentadoria
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_diasFaltando($idServidor) {
        
        # Conecta o banco de dados
        $intra = new Intra();
        $pessoal = new Pessoal();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);
        
        # Define a idade que dá direito para cada gênero
        switch ($sexo){
            case "Masculino" :
                $diasAposentadoria = $intra->get_variavel("diasAposentadoriaMasculino");
                break;
            case "Feminino" :
                $diasAposentadoria = $intra->get_variavel("diasAposentadoriaFeminino");
                break;
        }
        
        $tempoGeral = $this->get_tempoGeral($idServidor);
        $ocorrencias = $this->get_ocorrencias($idServidor);
        
        # Calcula o tempo de serviço geral
        $totalTempoGeral = $tempoGeral - $ocorrencias;

        # Dias que faltam
        $faltam = $diasAposentadoria - $totalTempoGeral;
        
        # Analisa resultado
        if($faltam < 0){
            $retorno = "($faltam)";
        }else{
            $retorno = $faltam;
        }
        
        return $retorno;
    }

    ###########################################################

    /**
     * Método podeAposentar
     * informa se o servidor pode aposentar baseado nos critérios de tempo de serviço e idade
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function podeAposentar($idServidor) {
        
        # Conecta o banco de dados
        $intra = new Intra();
        $pessoal = new Pessoal();
        
    #########################################################################
        
        # Verifica o tempo de serviço

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);
        
        # Define o tempo para cada gênero
        switch ($sexo){
            case "Masculino" :
                $diasAposentadoria = $intra->get_variavel("diasAposentadoriaMasculino");
                break;
            case "Feminino" :
                $diasAposentadoria = $intra->get_variavel("diasAposentadoriaFeminino");
                break;
        }
        
        $tempoGeral = $this->get_tempoGeral($idServidor);
        $ocorrencias = $this->get_ocorrencias($idServidor);
        
        # Calcula o tempo de serviço geral
        $totalTempoGeral = $tempoGeral - $ocorrencias;

        # Dias que faltam
        $faltam = $diasAposentadoria - $totalTempoGeral;
        
        # Analisa resultado
        if($faltam < 0){
            $tempo = TRUE;
        }else{
            $tempo = FALSE;
        }
        
    #########################################################################
        
        # Verifica a idade
        
        # Pega a idade do servidor
        $idade = $pessoal->get_idade($idServidor);
        
        # Define a idade para cada gênero
        switch ($sexo){
            case "Masculino" :
                $idadeAposentadoria = $intra->get_variavel("idadeAposentadoriaMasculino");
                break;
            case "Feminino" :
                $idadeAposentadoria = $intra->get_variavel("idadeAposentadoriaFeminino");
                break;
        }
        
        # Analisa o resultado
        if($idade >= $idadeAposentadoria){
            $idade = TRUE;
        }else{
            $idade = FALSE;
        }
        
        # Monta o retorno
        if(($tempo) AND ($idade)){
            $retorno = TRUE;
        }else{
            $retorno = FALSE;
        }
        
        return $retorno;
    }

    ###########################################################

}