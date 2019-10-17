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

    function exibeDadosConcurso($idConcurso = NULL){ 
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idConcurso)){
            $this->idConcurso = $idConcurso;
        }
        
        # Pega o ano base para colorir a tabela
        $dados = $this->get_dados();
        $anobase = $dados["anobase"];

        $select ='SELECT anobase,
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
        
        $label = array("Ano Base","Publicação <br/>do Edital","Regime","Tipo","Executor","Plano de Cargos");
        
        $formatacaoCondicional = array( array('coluna' => 0,
                                              'valor' => $anobase,
                                              'operador' => '=',
                                              'id' => 'listaDados'));
        
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_titulo("Concurso");
        $tabela->set_funcao(array(NULL,"date_to_php"));
        #$tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);
        
        $tabela->set_editar("?fase=editardeFato&id=".$this->idConcurso);
        $tabela->set_nomeColunaEditar("Editar");
        $tabela->set_idCampo('idConcurso');
                    
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
    
    ###########################################################


}
