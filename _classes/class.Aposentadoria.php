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
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
                break;
            case "Feminino" :
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
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
                ORDER BY tbpessoa.nome';

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
        $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

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
       $intra = new Intra();
       
       ##### Tempo Público
       
       # Tempo de Serviço
       $uenf = $this->get_tempoServicoUenf($idServidor);
       $publico = $this->get_tempoAverbadoPublico($idServidor);
       $publicoGeral = $uenf+$publico;
       
       # Pega a regra
       $diasAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
       $diasAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
       
       # Define a idade que dá direito para cada gênero
       $sexo = $pessoal->get_sexo($idServidor);
       switch ($sexo){
           case "Masculino" :
               $faltando = $diasAposentMasculino - $publicoGeral;
               break;
           
           case "Feminino" :
               $faltando = $diasAposentFeminino - $publicoGeral;
               break;
       }
              
       # calcula a data
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
                $idade = $intra->get_variavel("aposentadoria.integral.idade.masculino");
                break;
            case "Feminino" :
                $idade = $intra->get_variavel("aposentadoria.integral.idade.feminino");
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
        $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

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
        $diasAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
        $diasAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");
        
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
        $diasAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
        $diasAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
        
        # Monta o array
        $valores = array(array("Feminino",$idadeAposentFeminino,dias_to_diasMesAno($diasAposentFeminino)."<br/>($diasAposentFeminino dias)"),
                         array("Masculino",$idadeAposentMasculino,dias_to_diasMesAno($diasAposentMasculino)."<br/>($diasAposentMasculino dias)"));

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

    function exibeSomatorio(){

    /**
     * Exibe uma tabela com o somatório dos servidores ativos que já podem se aposentar
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Aposentadoria Integral
        $integralFerminino = $this->get_numServidoresAtivosPodemAposentarIntegral("Feminino");
        $integralMasculino = $this->get_numServidoresAtivosPodemAposentarIntegral("Masculino");
        $integralTotal = $integralFerminino + $integralMasculino;
        
        # Aposentadoria Proporcional
        $proporcionalFerminino = $this->get_numServidoresAtivosPodemAposentarProporcional("Feminino");
        $proporcionalMasculino = $this->get_numServidoresAtivosPodemAposentarProporcional("Masculino");
        $proporcionalTotal = $proporcionalFerminino + $proporcionalMasculino;
        
        # Aposentadoria Compulsória
        $compulsoriaFerminino = $this->get_numServidoresAtivosPodemAposentarCompulsoria("Feminino");
        $compulsoriaMasculino = $this->get_numServidoresAtivosPodemAposentarCompulsoria("Masculino");
        $compulsoriaTotal = $compulsoriaFerminino + $compulsoriaMasculino;
        
        $totalFeminino = $integralFerminino + $proporcionalFerminino + $compulsoriaFerminino;
        $totalMasculino = $integralMasculino + $proporcionalMasculino + $compulsoriaMasculino;
        $total = $integralTotal + $proporcionalTotal + $compulsoriaTotal;
        
        # Monta o array
        $valores = array(array("Feminino",$integralFerminino,$proporcionalFerminino,$compulsoriaFerminino,$totalFeminino),
                         array("Masculino",$integralMasculino,$proporcionalMasculino,$compulsoriaMasculino,$totalMasculino),
                         array("Total:",$integralTotal,$proporcionalTotal,$compulsoriaTotal,$total));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Somatório de Servidores Ativos que Podem se Aposentar");
        $tabela->set_label(array('Sexo','Integral','Proporcional','Compulsória','Total'));
        #$tabela->set_width(array(12,14,14,18,15,22));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array('left'));
        $tabela->set_conteudo($valores);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                            'valor' => "Total:",
                                            'operador' => '=',
                                            'id' => 'totalTempo')));
        $tabela->show();
    }
    
##############################################################################################################################################
	
    /**
     * Método get_numServidoresAtivosPodemAposentarIntegral
     * Informa o número de servidores que podem aposentar integralmente
     * 
     * @param string $parametroSexo sexo do servidor
     */

    public function get_numServidoresAtivosPodemAposentarIntegral($parametroSexo){

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select ='SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "'.$parametroSexo.'"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);
        
        $contador = 0;
        
        # Percorre o banco para verificar se já pode aposentar
        foreach($result as $lista){
            
            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaIntegral($lista[0]);
            
            # Verifica se a data colhida já passou
            if(jaPassou($data)){
                $contador++;
            }
        }
        
        return $contador;			
    }

##############################################################################################################################################
	
    /**
     * Método get_numServidoresAtivosPodemAposentarProporcional
     * Informa o número de servidores que podem aposentar Proporcional
     * 
     * @param string $parametroSexo sexo do servidor
     */

    public function get_numServidoresAtivosPodemAposentarProporcional($parametroSexo){

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select ='SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "'.$parametroSexo.'"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);
        
        $contador = 0;
        
        # Percorre o banco para verificar se já pode aposentar
        foreach($result as $lista){
            
            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaProporcional($lista[0]);
            
            # Verifica se a data colhida já passou
            if(jaPassou($data)){
                $contador++;
            }
        }
        
        return $contador;			
    }

##############################################################################################################################################
	
    /**
     * Método get_numServidoresAtivosPodemAposentarCompulsoria
     * Informa o número de servidores que podem aposentar Compulsória
     * 
     * @param string $parametroSexo sexo do servidor
     */

    public function get_numServidoresAtivosPodemAposentarCompulsoria($parametroSexo){

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Monta o select
        $select ='SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "'.$parametroSexo.'"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);
        
        $contador = 0;
        
        # Percorre o banco para verificar se já pode aposentar
        foreach($result as $lista){
            
            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaCompulsoria($lista[0]);
            
            # Verifica se a data colhida já passou
            if(jaPassou($data)){
                $contador++;
            }
        }
        
        return $contador;			
    }

##############################################################################################################################################

   /**
     * Método exibeTempo
     * Exibe tabela com o tempo de serviço do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function exibeTempo($idServidorPesquisado){
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Verifica a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
        $dtHoje = date("Y-m-d");                                      # Data de hoje
        $dtFinal = NULL;

        # Analisa a data
        if(!vazio($dtSaida)){           // Se tem saída é a saída
            $dtFinal = date_to_bd($dtSaida);
        }else{                          // Não tem saída então é hoje
            $dtFinal = $dtHoje;         
        }
        
        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);

        # Tempo de Serviço
        $uenf = $pessoal->get_tempoServicoUenf($idServidorPesquisado,$dtFinal);
        $publica = $pessoal->get_totalAverbadoPublico($idServidorPesquisado);
        $privada = $pessoal->get_totalAverbadoPrivado($idServidorPesquisado);
        $totalTempo = $uenf + $publica + $privada;
        
        ###
        
        $painel = new Callout();
        $painel->abre();

        titulo("Tempo de Serviço");
        br();
        
        ###
        
        # Verifica se tem sobreposição
        $selectSobreposicao = 'SELECT dtInicial, dtFinal, idAverbacao FROM tbaverbacao WHERE idServidor = '.$idServidorPesquisado.' ORDER BY dtInicial';
        $resultSobreposicao = $pessoal->select($selectSobreposicao);
        
        # Acrescenta o tempo de UENF
        $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));
        $resultSobreposicao[] = array($dtAdmissao,$dtFinal,NULL);

        # Inicia a variável que informa se tem sobreposicao
        $sobreposicao = FALSE;

        # Inicia o array que guarda os períodos problemáticos
        $idsProblemáticos[] = NULL;

        # Percorre os registros
        foreach($resultSobreposicao as $periodo){
            $dtInicial1 = date_to_php($periodo[0]);
            $dtFinal1 = date_to_php($periodo[1]);
            $idAverbado1 = $periodo[2];

            # Percorre a mesma listagem novamente
            foreach($resultSobreposicao as $periodoVerificado){

                $dtInicial2 = date_to_php($periodoVerificado[0]);
                $dtFinal2 = date_to_php($periodoVerificado[1]);
                $idAverbado2 = $periodoVerificado[2];

                # Evita que seja comparado com ele mesmo
                if($idAverbado1 <> $idAverbado2){
                    if(verificaSobreposicao($dtInicial1,$dtFinal1,$dtInicial2,$dtFinal2)){
                        $sobreposicao = TRUE;
                        $idsProblemáticos[] = $idAverbado1;
                        $idsProblemáticos[] = $idAverbado2;
                    }
                }
            }
        }

        if($sobreposicao){

            $painel = new Callout("alert","center");
            $painel->abre();
                echo "Atenção - Períodos com Sobreposição de Dias !!!";
                p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: ".date_to_php($dtAdmissao),"center","f11");
            $painel->fecha();
        }
        
        ###

        # Limita a tela
        $grid = new Grid();

        # Tempo público e privado
        $grid->abreColuna(4);

        # Monta o array
        $dados1 = array(
                array("UENF ",$uenf),
                array("Empresa Pública",$publica),
                array("Empresa Privada",$privada),
                array("Total",$totalTempo." dias<br/>(".dias_to_diasMesAno($totalTempo).")")
        );    

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Tempo de Serviço');
        $tabela->set_conteudo($dados1);
        $tabela->set_label(array("Descrição","Dias"));
        $tabela->set_align(array("left","center"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'totalTempo')));

        $tabela->show();            
        $grid->fechaColuna();

    #############################3

        # Ocorrências
        $grid->abreColuna(4);

        # Monta o select
        $reducao = "SELECT tbtipolicenca.nome as tipo,
                           SUM(numDias) as dias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = $idServidorPesquisado
                       AND tbtipolicenca.tempoServico IS TRUE
                  GROUP BY tbtipolicenca.nome";

        $dados2 = $pessoal->select($reducao);

        # Somatório
        $totalOcorrencias = array_sum(array_column($dados2, 'dias'));

        # Adiciona na tabela
        if($totalOcorrencias == 0){
            array_push($dados2,array("Sem Ocorrências","---"));
        }else{
            array_push($dados2,array("Total",$totalOcorrencias));
        }

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Ocorrências');
        $tabela->set_conteudo($dados2);
        $tabela->set_label(array("Descrição","Dias"));
        $tabela->set_align(array("left","center"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                       'valor' => "Total",
                                                       'operador' => '=',
                                                       'id' => 'totalTempo')
        ));

        $tabela->show();            
        $grid->fechaColuna();

    #############################3

        # Total do Tempo
        $grid->abreColuna(4); 

        # Calcula o tempo de serviço geral
        $totalTempoGeral = $totalTempo - $totalOcorrencias;

        # Monta o array
        $dados3 = array(
                  array("Tempo de Serviço ",$totalTempo),
                  array("Ocorrências","($totalOcorrencias)"),
                  array("Total",$totalTempoGeral)
        );

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Resumo Geral');
        $tabela->set_conteudo($dados3);
        $tabela->set_label(array("Descrição","Dias"));
        $tabela->set_align(array("left","center"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                    'valor' => "Total",
                                                    'operador' => '=',
                                                    'id' => 'totalTempo'),
                                                array('coluna' => 0,
                                                    'valor' => "Ocorrências",
                                                    'operador' => '=',
                                                    'id' => 'ocorrencia'),
                                                 array('coluna' => 0,
                                                       'valor' => "Dias Sobrando",
                                                       'operador' => '=',
                                                       'id' => 'diasSobrando'),
                                                 array('coluna' => 0,
                                                       'valor' => "Dias Faltando",
                                                       'operador' => '=',
                                                       'id' => 'diasFaltando')));
        $tabela->show();            
        $grid->fechaColuna();
        $grid->fechaGrid();

        $painel->fecha();
    }
    
##############################################################################################################################################
    
    /**
     * Método exibePrevisaoIntegral
     * Exibe análise da aposentadoria integral do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function exibePrevisaoIntegral($idServidorPesquisado){
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
        
        # Verifica a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
        $dtHoje = date("Y-m-d");                                      # Data de hoje
        $dtFinal = NULL;

        # Analisa a data
        if(!vazio($dtSaida)){           // Se tem saída é a saída
            $dtFinal = date_to_bd($dtSaida);
        }else{                          // Não tem saída então é hoje
            $dtFinal = $dtHoje;         
        }
        
        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);
        
        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);
        
        # Idade da Aposentadoria
        switch ($sexo){
            case "Masculino" :
                $anosAposentadoria = $intra->get_variavel("aposentadoria.integral.idade.masculino");
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
                break;
            case "Feminino" :
                $anosAposentadoria = $intra->get_variavel("aposentadoria.integral.idade.feminino");
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
                break;
        }
        
        # Tempo de Serviço
        $uenf = $this->get_tempoServicoUenf($idServidorPesquisado);
        $publica = $this->get_tempoAverbadoPublico($idServidorPesquisado);
        $privada = $this->get_tempoAverbadoPrivado($idServidorPesquisado);
        $ocorrencia = $this->get_ocorrencias($idServidorPesquisado);
        $totalTempo = ($uenf + $publica + $privada) - $ocorrencia;
        
        # Aposentadoria Integral
        $dtAposentadoriaIntegralIdade = $this->get_dataAposentadoriaIntegralIdade($idServidorPesquisado);
        $dtAposentadoriaIntegralTempo = $this->get_dataAposentadoriaIntegralTempo($idServidorPesquisado);
        $dtAposentadoriaIntegral = $this->get_dataAposentadoriaIntegral($idServidorPesquisado);
        $faltam = $diasAposentadoriaIntegral - $totalTempo;
        
        $painel = new Callout();
        $painel->abre();

        # Aposentadoria Integral

        # Monta o array
        $dados1 = array(
                  array("Idade",$anosAposentadoria,$idade),
                  array("Tempo de Serviço",$diasAposentadoriaIntegral,$totalTempo));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Aposentadoria Integral');
        $tabela->set_conteudo($dados1);
        $tabela->set_label(array("Descrição","Regra","Valor"));
        $tabela->set_align(array("left"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->show();
        
        if(jaPassou($dtAposentadoriaIntegral)){
            callout("Desde $dtAposentadoriaIntegral o servidor já pode solicitar Aposentadoria Integral!","success");
        }else{
            callout("Aposentadoria Integral somente em: $dtAposentadoriaIntegral.","warning");
        }

        # Análise por idade
        if($anosAposentadoria > $idade){
            p("O servidor ainda não alcançou os <b>$anosAposentadoria</b> anos de idade de para solicitar aposentadoria integral. Somente em $dtAposentadoriaIntegralIdade.","f14");
        }else{
            p("O servidor já alcançou a idade para solicitar aposentadoria integral.","f14");
        }

        # Análise por Tempo de Serviço
        if($diasAposentadoriaIntegral > $totalTempo){
            p("Ainda faltam <b>$faltam</b> dias para o servidor alcançar os <b>$diasAposentadoriaIntegral</b> dias de serviço necessários para solicitar a aposentadoria integral. Somente em $dtAposentadoriaIntegralTempo.","f14");
        }else{
            p("O servidor já alcançou os <b>$diasAposentadoriaIntegral</b> dias de tempo de serviço para solicitar aposentadoria integral.","f14");
        }
        
        ### sobreposição
        
        # Verifica se tem sobreposição
        $selectSobreposicao = 'SELECT dtInicial, dtFinal, idAverbacao FROM tbaverbacao WHERE idServidor = '.$idServidorPesquisado.' ORDER BY dtInicial';
        $resultSobreposicao = $pessoal->select($selectSobreposicao);
        
        # Acrescenta o tempo de UENF
        $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));
        $resultSobreposicao[] = array($dtAdmissao,$dtFinal,NULL);

        # Inicia a variável que informa se tem sobreposicao
        $sobreposicao = FALSE;

        # Inicia o array que guarda os períodos problemáticos
        $idsProblemáticos[] = NULL;

        # Percorre os registros
        foreach($resultSobreposicao as $periodo){
            $dtInicial1 = date_to_php($periodo[0]);
            $dtFinal1 = date_to_php($periodo[1]);
            $idAverbado1 = $periodo[2];

            # Percorre a mesma listagem novamente
            foreach($resultSobreposicao as $periodoVerificado){

                $dtInicial2 = date_to_php($periodoVerificado[0]);
                $dtFinal2 = date_to_php($periodoVerificado[1]);
                $idAverbado2 = $periodoVerificado[2];

                # Evita que seja comparado com ele mesmo
                if($idAverbado1 <> $idAverbado2){
                    if(verificaSobreposicao($dtInicial1,$dtFinal1,$dtInicial2,$dtFinal2)){
                        $sobreposicao = TRUE;
                        $idsProblemáticos[] = $idAverbado1;
                        $idsProblemáticos[] = $idAverbado2;
                    }
                }
            }
        }

        if($sobreposicao){

            $painel = new Callout("alert","center");
            $painel->abre();
                echo "Atenção - Períodos com Sobreposição de Dias !!!";
                p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: ".date_to_php($dtAdmissao),"center","f11");
            $painel->fecha();
        }

        $painel->fecha();
    }
    
    ##############################################################################################################################################
    
    /**
     * Método exibePrevisaoProporcional
     * Exibe análise da aposentadoria proporcional do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function exibePrevisaoProporcional($idServidorPesquisado){
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
        
        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);
        
        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);
        
        # Aposentadoria Proporcional
        $dtAposentadoriaProporcional = $this->get_dataAposentadoriaProporcional($idServidorPesquisado);
        $dtAposentadoriaProporcionalIdade = $this->get_dataProporcionalIdade($idServidorPesquisado);
        $dtAposentadoriaProporcionalTempo = $this->get_dataProporcionalTempo($idServidorPesquisado);
        
        # Idade da Aposentadoria
        switch ($sexo){
            case "Masculino" :
                $regraIdadeProporcional = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
                $regraTempoProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
                break;
            case "Feminino" :
                $regraIdadeProporcional = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
                $regraTempoProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
                break;
        }
        
        # Tempo de Serviço
        $uenf = $this->get_tempoServicoUenf($idServidorPesquisado);
        $publica = $this->get_tempoAverbadoPublico($idServidorPesquisado);
        $ocorrencia = $this->get_ocorrencias($idServidorPesquisado);
        $totalPublico = ($uenf + $publica) - $ocorrencia;
        
        $painel = new Callout();
        $painel->abre();

        # Monta o array
        $dados1 = array(
                  array("Idade",$regraIdadeProporcional,$idade),
                  array("Tempo de Serviço Público",$regraTempoProporcional,$totalPublico));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Aposentadoria Proporcional');
        $tabela->set_conteudo($dados1);
        $tabela->set_label(array("Descrição","Regra","Valor"));
        $tabela->set_align(array("left"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->show();
        
        if(jaPassou($dtAposentadoriaProporcional)){
            callout("Desde $dtAposentadoriaProporcional o servidor já pode solicitar Aposentadoria Proporcional!","success");
        }else{
            callout("Aposentadoria Proporcional somente em: $dtAposentadoriaProporcional.","warning");
        }

        # Dias que faltam
        $faltamProporcional = $regraTempoProporcional - $totalPublico;

        # Análise por idade
        if($regraIdadeProporcional > $idade){
            p("O servidor ainda não alcançou os <b>$regraIdadeProporcional</b> anos de idade de para solicitar aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalIdade.","f14");
        }else{
            p("O servidor já alcançou a idade para solicitar aposentadoria proporcional.","f14");
        }

        # Análise por Tempo de Serviço
        if($regraTempoProporcional > $totalPublico){
            p("Ainda faltam <b>$faltamProporcional</b> dias para o servidor alcançar os <b>$regraTempoProporcional</b> dias de serviço necessários para solicitar a aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalTempo.","f14");
        }else{
            p("O servidor já alcançou os <b>$regraTempoProporcional</b> dias de tempo serviço público para solicitar aposentadoria proporcional.","f14");
        }

        $painel->fecha();
    }
    
    ##############################################################################################################################################
    
    /**
     * Método exibePrevisaoCompulsoria
     * Exibe análise da aposentadoria compulsória
     * 
     * @param string $idServidor idServidor do servidor
     */

    public function exibePrevisaoCompulsoria($idServidorPesquisado){
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();
        
        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);
        
        # Aposentadoria Compulsória
        $dtAposentadoriaCompulsoria = $this->get_dataAposentadoriaCompulsoria($idServidorPesquisado);
        $idadeAposentadoriaCompulsoria = $intra->get_variavel("aposentadoria.compulsoria.idade");
    
        $painel = new Callout();
        $painel->abre();

        # Monta o array
        $dados1 = array(
                  array("Idade",$idadeAposentadoriaCompulsoria,$idade));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_titulo('Aposentadoria Compulsória');
        $tabela->set_conteudo($dados1);
        $tabela->set_label(array("Descrição","Regra","Valor"));
        $tabela->set_align(array("left"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->show();
        
        br(2);
        
        if(jaPassou($dtAposentadoriaCompulsoria)){
            callout("Desde $dtAposentadoriaCompulsoria o servidor terá que se aposentar compulsoriamente!","success");
        }else{
            callout("Aposentadoria Compulsória somente em: $dtAposentadoriaCompulsoria.","warning");
        }

        # Análise por idade
        if(75 > $idade){
            p("O servidor ainda não alcançou os <b>$idadeAposentadoriaCompulsoria</b> anos de idade de para a aposentadoria compulsória. Somente em $dtAposentadoriaCompulsoria.","f14");
        }else{
            p("O servidor já alcançou a idade para a aposentadoria compulsória.","f14");
        }

        $painel->fecha();
    }
}