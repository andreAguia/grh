<?php
class Sispatri{
 /**
  * Abriga as várias rotina do COntrole Sispatri
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    private $lotacao = NULL;
    private $situacao = NULL;
        
###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }

###########################################################
	
    /**
     * Método set_lotacao
     * 
     * @param $lotacao 
     */
    
    public function set_lotacao($lotacao){
        $this->lotacao = $lotacao;
    }

###########################################################
	
    /**
     * Método set_situacao
     * 
     * @param  	$situacao
     */
    
    public function set_situacao($situacao){
        $this->situacao = $situacao;
    }

###########################################################
    
    public function get_servidores(){
    
        # Pega os dados
        $select ='SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        # Situação
        if($this->situacao <> "Todos"){
            $select .= ' AND tbservidor.situacao = '.$this->situacao;
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);
    }
    
    ###########################################################
    
    public function get_numServidores(){
    
        # Pega os dados
        $select ='SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        # Situação
        if($this->situacao <> "Todos"){
            $select .= ' AND tbservidor.situacao = '.$this->situacao;
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->count($select);
    }
    
    ###########################################################
}