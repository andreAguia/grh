<?php
class Concurso
{
 /**
  * Abriga as várias rotina referentes a concurso
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $idConcurso integer NULL O id do concurso
  */
    
    private $idConcurso = NULL;

##############################################################

    public function __construct($idConcurso = NULL){
    /**
     * Inicia a Classe somente
     * 
     * @param $idConcurso integer NULL O id do concurso
     * 
     * @syntax $concurso = new Concurso([$idConcurso]);
     */
        
        $this->idConcurso = $idConcurso;
    }
  
##############################################################

    public function get_dados($idConcurso = NULL){

    /**
     * Informa os dados da base de dados
     * 
     * @param $idConcurso integer NULL O id do concurso
     * 
     * @syntax $concurso->get_dados([$idConcurso]);
     */
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idConcurso)){
            $this->idConcurso = $idConcurso;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($this->idConcurso)){
            alert("É necessário informar o id do Concurso.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbconcurso
                    WHERE idConcurso = '.$this->idConcurso;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

###########################################################

    /**
     * Método exibeDadosConcurso
     * fornece os dados de uma vaga em forma de tabela
     * 
     * @param	string $idVaga O id da vaga
     */

    function exibeDadosConcurso($idConcurso = NULL,$editar = FALSE){ 
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idConcurso)){
            $this->idConcurso = $idConcurso;
        }
        
        # Pega o ano base para colorir a tabela
        $dados = $this->get_dados();
        $anobase = $dados["anobase"];

        $select ='SELECT idConcurso,
                         anobase,
                         dtPublicacaoEdital,
                         regime,
                         CASE tipo
                           WHEN 1 THEN "Adm & Tec"
                           WHEN 2 THEN "Professor"
                           ELSE "--"
                         END,
                         orgExecutor,
                         tbplano.numDecreto,
                         idConcurso
                    FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                   WHERE idConcurso = '.$this->idConcurso;
        
        $conteudo = $servidor->select($select,TRUE);
        
        $label = array("Id","Ano Base","Publicação <br/>do Edital","Regime","Tipo","Executor","Plano de Cargos");
        
        $formatacaoCondicional = array( array('coluna' => 1,
                                              'valor' => $anobase,
                                              'operador' => '=',
                                              'id' => 'listaDados'));
        
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_titulo("Concurso");
        $tabela->set_funcao(array(NULL,NULL,"date_to_php"));
        #$tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);
        
        if($editar){
            $tabela->set_editar("?fase=editardeFato&id=".$this->idConcurso);
            $tabela->set_nomeColunaEditar("Editar");
            $tabela->set_idCampo('idConcurso');
        }
                    
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);
        
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid(); 
    }

    ###########################################################
    
    public function exibeEdital($idConcurso){
    /**
     * Exibe um link para o edital
     * 
     * @param $idConcurso integer NULL O id do Concurso
     * 
     * @syntax $plano->exibeEdital($idConcurso);
     */
        
        # Monta o arquivo
        $arquivo = "../../_editais/".$idConcurso.".pdf";
        
        # Verifica se ele existe
        if(file_exists($arquivo)){
            
            # Monta o link
            $link = new Link(NULL,$arquivo,"Exibe o Edital");
            $link->set_imagem(PASTA_FIGURAS_GERAIS."ver.png",20,20);
            $link->set_target("_blank");
            $link->show();
            
        }else{
            echo "-";
        }
    }
    
    #####################################################################################
	
	/**
	 * Método get_nomeConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_nomeConcurso($idconcurso){
            
            # Monta o select            
            $select = 'SELECT CONCAT(tbconcurso.anoBase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as cc                
                         FROM tbconcurso
                        WHERE idconcurso = '.$idconcurso;
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->select($select,FALSE);
             return $row[0];
	}

    ###########################################################
    
    public function exibeBotaoUpload($idConcurso){
    /**
     * Exibe um link para exibir o edital
     * 
     * @param $idconcurso integer NULL O id do plano
     * 
     * @syntax $plano->exibeLei($idPlano);
     */
        $link = new Link(NULL,"?fase=uploadEdital&id=".$idConcurso,"Upload o Edital");
        $link->set_imagem(PASTA_FIGURAS."upload.png",20,20);
        #$link->set_target("_blank");
        $link->show();     
       
    }
    
    ###########################################################
    
    public function exibeQuadroDocentesSemConcurso(){
    /**
     * Exibe um quadro com os docentes sem concurso
     * 
     * @syntax $plano->exibeQuadroDocentesSemConcurso();
     */
        
        $ativosS = $this->get_numDocentesAtivosSemConcurso();
        $inativosS = $this->get_numDocentesInativosSemConcurso();
        $totals = $ativosS+$inativosS;
        
        $ativosC = $this->get_numDocentesAtivosComConcurso();
        $inativosC = $this->get_numDocentesInativosComConcurso();
        $totalc = $ativosC+$inativosC;
        
        # conteúdo
        $array = array(array("Ativos",$ativosS,$ativosC,$ativosS+$ativosC),
                       array("Inativos",$inativosS,$inativosC,$inativosS+$inativosC),
                       array("Total",$totals,$totalc,$totals+$totalc));

       
        # Exemplo de tabela simples
        $tabela = new Tabela();
        $tabela->set_titulo("Professores");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Tipo","Sem Concurso","Com Concurso","Total"));
        #$tabela->set_width(array(80,20));
        $tabela->set_align(array("left","center"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalVagas')));
        $tabela->show();
    }
    
    #####################################################################################
	
	/**
	 * Método get_numDocentesAtivosSemConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_numDocentesAtivosSemConcurso(){
            
            # Monta o select            
            $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NULL
                          AND tbservidor.situacao = 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
	
	/**
	 * Método get_numDocentesInativosSemConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_numDocentesInativosSemConcurso(){
            
            # Monta o select            
            $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NULL
                          AND tbservidor.situacao <> 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
	
	/**
	 * Método get_numDocentesAtivosSemConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_numDocentesAtivosComConcurso(){
            
            # Monta o select            
            $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NOT NULL
                          AND tbservidor.situacao = 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
	
	/**
	 * Método get_numDocentesAtivosSemConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_numDocentesInativosComConcurso(){
            
            # Monta o select            
            $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NOT NULL
                          AND tbservidor.situacao <> 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
	
	/**
	 * Método get_numVagasConcurso
	 * 
	 * Informa o numero de vagas por concurso
         */
	
	public function get_numVagasConcurso($idConcurso){
            
            # Joga o valor informado para a variável da classe
            if(!vazio($idConcurso)){
                $this->idConcurso = $idConcurso;
            }
            
            # Monta o select            
            $select = "SELECT idVagaHistorico
                         FROM tbvagahistorico
                        WHERE idConcurso = $this->idConcurso";
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
	
	/**
	 * Método get_numPublicacaoConcurso
	 * 
	 * Informa o numero de publicação por concurso
         */
	
	public function get_numPublicacaoConcurso($idConcurso){
            
            # Joga o valor informado para a variável da classe
            if(!vazio($idConcurso)){
                $this->idConcurso = $idConcurso;
            }
            
            # Monta o select            
            $select = "SELECT idConcursoPublicacao
                         FROM tbconcursopublicacao
                        WHERE idConcurso = $this->idConcurso";
           
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
	}

    #####################################################################################
}
