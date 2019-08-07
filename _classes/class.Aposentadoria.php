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
                    WHERE situacao = 2
                    AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)';
        
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
            $retorno = $faltam;
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

    function get_numEstatutariosPodemAposentar($sexo = NULL){

    /**
     * informa o número de Servidores Ativos que já alcançaram os requisitos para aposentadoria
     * 
     * @param integer $idPessoa do servidor
     */
        
        $select = 'SELECT idServidor
                     FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                    WHERE tbservidor.situacao = 1
                      AND idPerfil = 1
                      AND tbpessoa.sexo = "'.$sexo.'"';
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);
        
        $contador = 0;
        foreach($row as $servidor){
            if($this->podeAposentar($servidor[0])){
                $contador++;
            }
        }
        
        return $contador;
    }

    ###########################################################

    function exibeResumoPrevisao(){

    /**
     * Exibe uma tabela com as informações de previsão de aposentadoria dos servidorea ativos
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
    
        # Pega os valores de referência
        $diasAposentMasculino = $intra->get_variavel("diasAposentadoriaMasculino");
        $diasAposentFeminino = $intra->get_variavel("diasAposentadoriaFeminino");
        $idadeAposentMasculino = $intra->get_variavel("idadeAposentadoriaMasculino");
        $idadeAposentFeminino = $intra->get_variavel("idadeAposentadoriaFeminino");
        $compulsoria = $intra->get_variavel("idadeAposentadoriaCompulsoria");

        $numEstatutariosFeminino = $pessoal->get_numEstatutariosAtivosSexo("Feminino");
        $numEstatutariosMasculino = $pessoal->get_numEstatutariosAtivosSexo("Masculino");

        $jaPodemFeminino = $this->get_numEstatutariosPodemAposentar("Feminino");
        $jaPodemMasculino = $this->get_numEstatutariosPodemAposentar("Masculino");

        $valores = array(array("Feminino",$idadeAposentFeminino,$compulsoria,$diasAposentFeminino." (".dias_to_diasMesAno($diasAposentFeminino).")",$numEstatutariosFeminino,$jaPodemFeminino),
                         array("Masculino",$idadeAposentMasculino,$compulsoria,$diasAposentMasculino." (".dias_to_diasMesAno($diasAposentMasculino).")",$numEstatutariosMasculino,$jaPodemMasculino));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_label(array('Sexo','Idade para Aposentar','Idade para a Compulsória','Tempo de Serviço para Aposentar','Número de Estatutários','Número de Servidores que Podem Aposentar'));
        $tabela->set_width(array(12,14,14,18,15,22));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array('left'));
        $tabela->set_conteudo($valores);
        $tabela->show();
    }
    
###########################################################

    function exibeAposentadosPorAno($parametroAno = NULL){

    /**
     * Exibe tabela com os aposentados por ano de aposentadoria
     * 
     * @param integer $parametroAno da aposentadoria
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Trata o parametro do ano
        if(is_null($parametroAno)){
            $parametroAno = date('Y');
        }
        
        # Monta o select
        $select = 'SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbmotivo.motivo
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE YEAR(tbservidor.dtDemissao) = "'.$parametroAno.'"
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';


        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados em '.$parametroAno);
        $tabela->set_tituloLinha2('Com Informaçao de Contatos');
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída','Motivo'));
        #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
        $tabela->set_align(array('center','left','left','center','center','left'));
        $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array(NULL,NULL,"pessoal"));
        $tabela->set_metodo(array(NULL,NULL,"get_cargo"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarAno');
        $tabela->show();

    }    
    
    ###########################################################

    function exibeAposentadosPorTipo($parametroMotivo = NULL){

    /**
     * Exibe tabela com os aposentados por tipo de aposentadoria
     * 
     * @param string $parametroMotivo da aposentadoria
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select = 'SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE tbservidor.motivo = '.$parametroMotivo.'
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';


        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados por Tipo');
        $tabela->set_tituloLinha2('Com Informaçao de Contatos');
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída','Perfil'));
        #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
        $tabela->set_align(array('center','left','left'));
        $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,"pessoal"));
        $tabela->set_metodo(array(NULL,NULL,"get_cargo",NULL,NULL,"get_perfil"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarMotivo');
        $tabela->show();
    }    
    
    ###########################################################

    function exibeAtivosPrevisao($parametroSexo = NULL){

    /**
     * Exibe tabela com a previsão de aposentadoria de servidores ativos
     * 
     * @param string $parametroSexo o sexo do servidor
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select ='SELECT tbservidor.idServidor,
                         tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,                         
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "'.$parametroSexo.'"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: '.$parametroSexo);
        $tabela->set_subtitulo('Servidores do Sexo '.$parametroSexo);
        $tabela->set_label(array('','IdFuncional','Nome','Cargo','Idade','Data da Aposentadoria por idade','Data da Compulsória',"Tempo Serviço (dias)","Ocorrências (dias)","Dias Faltando",'Data da Aposentadoria por Tempo'));
        $tabela->set_width(array(5,10,10,10,5,10,10,5,5,10));
        $tabela->set_align(array("center","center","left","left"));
        #$tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array("Aposentadoria",NULL,NULL,"pessoal","pessoal","Aposentadoria","Aposentadoria","Aposentadoria","Aposentadoria","Aposentadoria","Aposentadoria"));
        $tabela->set_metodo(array("podeAposentar",NULL,NULL,"get_Cargo","get_idade","get_dataAposentadoria","get_dataCompulsoria","get_tempoGeral","get_ocorrencias","get_diasFaltando","get_dataAposentadoriaTS"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarPrevisao');

        $tabela->set_formatacaoCondicional(array(
                                              array('coluna' => 0,
                                                    'valor' => TRUE,
                                                    'operador' => '=',
                                                    'id' => 'pode'),
                                              array('coluna' => 0,
                                                    'valor' => FALSE,
                                                    'operador' => '=',
                                                    'id' => 'naoPode')
                                                    ));

        $pode = new Imagem(PASTA_FIGURAS.'accept.png','Pode Aposentar',15,15);
        $naoPode = new Imagem(PASTA_FIGURAS.'bloqueado2.png','Ainda Tem Pendências',15,15);

        $tabela->set_imagemCondicional(array(array('coluna' => 0,
                                                   'valor' => TRUE,
                                                   'operador' => '=',
                                                   'imagem' => $pode),
                                             array('coluna' => 0,
                                                   'valor' => FALSE,
                                                   'operador' => '=',
                                                   'imagem' => $naoPode)
                                        ));
        $tabela->show();
    } 
    
###########################################################

   /**
    * Método get_dataAposentadoria
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoria($idServidor){
       
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
        
        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);
        
        # Define a idade que dá direito para cada gênero
        switch ($sexo){
            case "Masculino" :
                $idade = $intra->get_variavel("idadeAposentadoriaMasculino");
                break;
            case "Feminino" :
                $idade = $intra->get_variavel("idadeAposentadoriaFeminino");
                break;
        }

        # Pega a data de nascimento (vem dd/mm/AAAA)
        $dtNasc = $pessoal->get_dataNascimento($idServidor);
        $partes = explode("/",$dtNasc);

        # Soma
        $novoAno  = $partes[2]+$idade;

        # Calcula a data
        $novaData = $partes[0]."/".$partes[1]."/".$novoAno;            
        return $novaData;			
   }

   ###########################################################
	
    /**
     * Método get_dataCompulsoria
     * Informa a data em que o servidor é obrigado a se aposentar
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function get_dataCompulsoria($idServidor){

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Idade obrigatória
        $idade = $intra->get_variavel("idadeAposentadoriaCompulsoria");

        # Pega a data de nascimento (vem dd/mm/AAAA)
        $dtNasc = $pessoal->get_dataNascimento($idServidor);
        $partes = explode("/",$dtNasc);

        # Soma
        $novoAno  = $partes[2]+$idade;

        # Calcula a data
        $novaData = $partes[0]."/".$partes[1]."/".$novoAno;            
        return $novaData;			
    }

    ###########################################################

   /**
    * Método get_dataAposentadoriaTS
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria considerando o tempo de serviço
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoriaTS($idServidor){
       
       # Pega os dias faltando
       $faltando = $this->get_diasFaltando($idServidor);
       
       $novaData = addDias(date("d/m/Y"),$faltando,FALSE);
       
       return $novaData;			
   }

   ###########################################################

   /**
    * Método get_dataProporcional
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataProporcional($idServidor){
       
       
       # Conecta ao Banco de Dados
       $pessoal = new Pessoal();
       
       ##### Tempo Público
       
       # Tempo de Serviço
       $uenf = $this->get_tempoServicoUenf($idServidor);
       $publico = $this->get_tempoAverbadoPublico($idServidor);
       $publicoGeral = $uenf+$publico;
              
       # Verifica se o tempo público é maior que 10 anos (3650 dias)
       $dezAnos = 3650;
       $faltando = $dezAnos - $publicoGeral;
       $dataPublico = addDias(date("d/m/Y"),$faltando,FALSE);
       
       ##### Idade
       
       # Define a idade que dá direito para cada gênero
       $sexo = $pessoal->get_sexo($idServidor);
       switch ($sexo){
           case "Masculino" :
               $dataIdade = $pessoal->get_dataIdade($idServidor,65);
               break;
           
           case "Feminino" :
               $dataIdade = $pessoal->get_dataIdade($idServidor,60);
               break;
       }
        
       if($dataIdade > $dataPublico){
           $novaData = $dataPublico;
       }else{
           $novaData = $dataIdade;
       }
       
       return $novaData;			
   }

   ###########################################################  
}