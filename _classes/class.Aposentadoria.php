<?php
class Aposentadoria{
 /**
  * Abriga as várias rotina referentes a aposentadoria do servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    
##############################################################################################################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }
    
##############################################################################################################################################

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

##############################################################################################################################################
    
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
    
##############################################################################################################################################

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
    
##############################################################################################################################################

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

##############################################################################################################################################

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

##############################################################################################################################################

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

##############################################################################################################################################

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

##############################################################################################################################################

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

##############################################################################################################################################

    /**
     * Método get_diasFaltandoIntegral
     * informa o total de dias que faltam para completar o tempo de serviço para aposentadoria Integral
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_diasFaltandoIntegral($idServidor) {
        
        # Conecta o banco de dados
        $intra = new Intra();
        $pessoal = new Pessoal();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);
        
        # Define a idade que dá direito para cada gênero
        switch ($sexo){
            case "Masculino" :
                $diasAposentadoriaIntegral = $intra->get_variavel("diasAposentadoriaMasculino");
                break;
            case "Feminino" :
                $diasAposentadoriaIntegral = $intra->get_variavel("diasAposentadoriaFeminino");
                break;
        }
        
        $tempoGeral = $this->get_tempoGeral($idServidor);
        $ocorrencias = $this->get_ocorrencias($idServidor);
        
        # Calcula o tempo de serviço geral
        $totalTempoGeral = $tempoGeral - $ocorrencias;

        # Dias que faltam
        $faltam = $diasAposentadoriaIntegral - $totalTempoGeral;
        
        # Analisa resultado
        if($faltam < 0){
            $retorno = $faltam;
        }else{
            $retorno = $faltam;
        }
        
        return $retorno;
    }
    
##############################################################################################################################################

    function exibeAtivosPrevisao($parametroSexo = NULL){

    /**
     * Exibe tabela com a previsão de aposentadoria de servidores ativos
     * 
     * @param string $parametroSexo o sexo do servidor
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select ='SELECT tbservidor.idFuncional,
                         tbpessoa.nome,
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
        $tabela->set_label(array('IdFuncional','Nome','Cargo','Integral','Proporcional','Compulsória'));
        #$tabela->set_width(array(30,15,15,15,15));
        $tabela->set_align(array("center","left","left"));
        $tabela->set_funcaoDepoisClasse(array(NULL,NULL,NULL,"marcaSePassou","marcaSePassou","marcaSePassou"));

        $tabela->set_classe(array(NULL,NULL,"pessoal","Aposentadoria","Aposentadoria","Aposentadoria"));
        $tabela->set_metodo(array(NULL,NULL,"get_CargoSimples","get_dataAposentadoriaIntegral","get_dataAposentadoriaProporcional","get_dataAposentadoriaCompulsoria"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarPrevisao');
        $tabela->show();
    } 
    
##############################################################################################################################################	
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

        # Calcula a data
        $novaData  = addAnos($dtNasc,$idade);
        
        return $novaData;			
    }

##############################################################################################################################################

   /**
    * Método get_dataProporcional
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional pela Idade
    * 
    * @param integer $idServidor idServidor do servidor
    */

   public function get_dataProporcionalIdade($idServidor){
       
       
       # Conecta ao Banco de Dados
       $pessoal = new Pessoal();
       
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
       
       return $dataIdade;			
   }
   
##############################################################################################################################################

   /**
    * Método get_dataProporcionalTempo
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional pelo Tempo de Serviço Público
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataProporcionalTempo($idServidor){
       
       
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
       
       return $dataPublico;			
   }
   
##############################################################################################################################################

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
        
       $novaData = dataMaior($dataIdade,$dataPublico);
       
       return $novaData;			
   }
   
##############################################################################################################################################


   /**
    * Método get_dataAposentadoriaIntegral
    * Informa se pode aposentar de forma integral
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoriaIntegral($idServidor){
       
        # Idade
        $dataIdade = $this->get_dataAposentadoriaIntegralIdade($idServidor);
       
        # Tempo de Serviço
        $dataTempo = $this->get_dataAposentadoriaIntegralTempo($idServidor);
        
        $novaData = dataMaior($dataIdade,$dataTempo);
       
        return $novaData;
   }

##############################################################################################################################################

   /**
    * Método get_dataAposentadoriaIntegralIdade
    * Informa a data em que o servidor passa a ter idade para solicitar aposentadoria integral
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoriaIntegralIdade($idServidor){
       
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

        # Calcula a data
        $novaData  = addAnos($dtNasc,$idade);
        
        return $novaData;			
   }

##############################################################################################################################################

   /**
    * Método get_dataAposentadoriaIntegralTempo
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria integral considerando o tempo de serviço
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoriaIntegralTempo($idServidor){
       
       # Pega os dias faltando
       $faltando = $this->get_diasFaltandoIntegral($idServidor);
       
       $novaData = addDias(date("d/m/Y"),$faltando,FALSE);
       
       return $novaData;			
   }

##############################################################################################################################################

   /**
    * Método get_dataAposentadoriaProporcional
    * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional
    * 
    * @param string $idServidor idServidor do servidor
    */

   public function get_dataAposentadoriaProporcional($idServidor){
       
       
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
        
       $novaData = dataMaior($dataIdade,$dataPublico);
       
       return $novaData;			
   }
##############################################################################################################################################
	
    /**
     * Método get_dataAposentadoriaCompulsoria
     * Informa a data em que o servidor é obrigado a se aposentar
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function get_dataAposentadoriaCompulsoria($idServidor){

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Idade obrigatória
        $idade = $intra->get_variavel("idadeAposentadoriaCompulsoria");

        # Pega a data de nascimento (vem dd/mm/AAAA)
        $dtNasc = $pessoal->get_dataNascimento($idServidor);

        # Calcula a data
        $novaData  = addAnos($dtNasc,$idade);
        
        return $novaData;			
    }

##############################################################################################################################################

    function exibeRegras(){

    /**
     * Exibe uma tabela com as regras da aposentadoria
     */
        
        $painel = new Callout("secondary");
        $painel->abre();

        titulo("Regras");
        br();
    
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
        
        # Abre o Grid
        $grid = new Grid();
        $grid->abreColuna(4);
        
        # Aposentadoria Integral
        $diasAposentMasculino = $intra->get_variavel("diasAposentadoriaMasculino");
        $diasAposentFeminino = $intra->get_variavel("diasAposentadoriaFeminino");
        $idadeAposentMasculino = $intra->get_variavel("idadeAposentadoriaMasculino");
        $idadeAposentFeminino = $intra->get_variavel("idadeAposentadoriaFeminino");
        
        # Monta o array
        $valores = array(array("Feminino",$idadeAposentFeminino,dias_to_diasMesAno($diasAposentFeminino)."<br/>($diasAposentFeminino dias)"),
                         array("Masculino",$idadeAposentMasculino,dias_to_diasMesAno($diasAposentMasculino)."<br/>($diasAposentMasculino dias)"));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Aposentadoria Integral");
        $tabela->set_label(array('Sexo','Idade','Tempo de Serviço'));
        #$tabela->set_width(array(12,14,14,18,15,22));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array('left'));
        $tabela->set_conteudo($valores);
        $tabela->show();
        
        $grid->fechaColuna();
        $grid->abreColuna(4);
        
        # Aposentadoria Proporcional
        $diasAposentMasculino = "10a";
        $diasAposentFeminino = "10a";
        $idadeAposentMasculino = 65;
        $idadeAposentFeminino = 60;
        
        # Monta o array
        $valores = array(array("Feminino",$idadeAposentFeminino,$diasAposentFeminino."<br/>(3650 dias)"),
                         array("Masculino",$idadeAposentMasculino,$diasAposentMasculino."<br/>(3650 dias)"));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Aposentadoria Proporcional");
        $tabela->set_label(array('Sexo','Idade','Tempo de Serviço Público'));
        #$tabela->set_width(array(12,14,14,18,15,22));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array('left'));
        $tabela->set_conteudo($valores);
        $tabela->show();
        
        $grid->fechaColuna();
        $grid->abreColuna(4);
        
        # Aposentadoria Compulsória
        
        # Monta o array
        $valores = array(array("Feminino",75),
                         array("Masculino",75));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Aposentadoria Compulsória");
        $tabela->set_label(array('Sexo','Idade'));
        #$tabela->set_width(array(12,14,14,18,15,22));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array('left'));
        $tabela->set_conteudo($valores);
        $tabela->show();
        
        $grid->fechaColuna();
        $grid->fechaGrid();
        
        $painel->fecha();
    }
    
##############################################################################################################################################
    
}