<?php
class ConcursoPublicacao
{
 /**
  * Abriga as várias rotina referentes as publicações de concurso
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $idConcursoPublicacao integer NULL O id da publicação
  */
    
    private $idConcursoPublicacao = NULL;

##############################################################

    public function __construct($idConcursoPublicacao = NULL){
    /**
     * Inicia a Classe somente
     * 
     * @param $idConcursoPublicacao integer NULL O id do concurso
     * 
     * @syntax $ConcursoPublicacao = new ConcursoPublicacao([$idConcursoPublicacao]);
     */
        
        $this->idConcursoPublicacao = $idConcursoPublicacao;
    }
  
##############################################################

    public function get_dados($idConcursoPublicacao = NULL){

    /**
     * Informa os dados da base de dados
     * 
     * @param $idConcursoPublicacao integer NULL O id do concurso
     * 
     * @syntax $ConcursoPublicacao->get_dados([$idConcursoPublicacao]);
     */
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idConcursoPublicacao)){
            $this->idConcursoPublicacao = $idConcursoPublicacao;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($this->idConcursoPublicacao)){
            alert("É necessário informar o id do Publicação.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbconcursopublicacao
                    WHERE idConcursoPublicacao = '.$this->idConcurso;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

###########################################################
    
    public function exibePublicacao($idConcursoPublicacao){
    /**
     * Exibe um link para a publicação
     * 
     * @param $idConcursoPublicacao integer NULL O id do Concurso
     * 
     * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
     */
        
        # Monta o arquivo
        $arquivo = "../../_concursoPublicacoes/".$idConcursoPublicacao.".pdf";
        
        # Verifica se ele existe
        if(file_exists($arquivo)){
            
            # Monta o link
            $link = new Link(NULL,$arquivo,"Exibe a Publicação");
            $link->set_imagem(PASTA_FIGURAS."publicacao.png",20,20);
            $link->set_target("_blank");
            $link->show();
            
        }else{
            echo "-";
        }
    }
    
###########################################################


}
