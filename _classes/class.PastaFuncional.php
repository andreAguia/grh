<?php
class PastaFuncional
{
 /**
  * Abriga as várias rotina referentes a Controle de pasta funcional de um servidor
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $idPasta integer NULL O id do documento da pasta do servidor
  */
    
    private $idPasta = NULL;

##############################################################

    public function __construct($idPasta = NULL){
    /**
     * Inicia a Classe somente
     * 
     * @param $idPasta integer NULL O id do documento
     * 
     * @syntax $pastaFuncional = new PastaFuncional([$idPasta]);
     */
        
        $this->idPasta = $idPasta;
    }
  
##############################################################

    public function get_dados($idPasta = NULL){

    /**
     * Informa os dados da base de dados
     * 
     * @param $idPasta integer NULL O id do $idPasta
     * 
     * @syntax $pastaFuncional->get_dados([$idPasta]);
     */
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idPasta)){
            $this->idPasta = $idPasta;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($this->idPasta)){
            alert("É necessário informar o id do Documento da Pasta Funcional.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbpasta
                    WHERE idPasta = '.$this->idPasta;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

###########################################################
    
    public function exibePasta($id){
    /**
     * Exibe um link para a publicação
     * 
     * @param $idConcursoPublicacao integer NULL O id do Concurso
     * 
     * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
     */
        
        # Monta o arquivo
        $arquivo = PASTA_FUNCIONAL.$id.".pdf";
        
        # Verifica se ele existe
        if(file_exists($arquivo)){
            
            # Monta o link
            $link = new Link(NULL,$arquivo,"Exibe o MCF");
            $link->set_imagem(PASTA_FIGURAS."ver.png",20,20);
            $link->set_target("_blank");
            $link->show();
            
        }else{
            echo "-";
        }
    }
    
###########################################################
  
}
