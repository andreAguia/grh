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
        if($lotacao <> "Todos"){
            $this->lotacao = $lotacao;
        }
    }

###########################################################
	
    /**
     * Método set_situacao
     * 
     * @param  	$situacao
     */
    
    public function set_situacao($situacao){
        if($situacao <> "Todos"){
            $this->situacao = $situacao;
        }
    }

###########################################################
    
    public function get_servidoresAtivos(){
    
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
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao = 1';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);
        
        return $retorno;
    }
    
###########################################################
    
    public function get_servidoresNaoAtivos(){
    
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
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao <> 1';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);
        
        return $retorno;
    }
    
###########################################################
    
    public function get_numServidoresAtivos(){
    
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
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao = 1';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->count($select);
        
        return $retorno;
    }
    
###########################################################
    
    public function get_numServidoresNaoAtivos(){
    
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
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbservidor.situacao <> 1';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->count($select);
        
        return $retorno;
    }
    
###########################################################
    
    public function get_servidoresRelatorio(){
    
        # Pega os dados
        $select ='SELECT tbservidor.idfuncional,
                         tbpessoa.nome
                    FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Lotacao
        if(!vazio($this->lotacao)){
            # Verifica se o que veio é numérico
            if(is_numeric($this->lotacao)){
                $select .= ' AND (tblotacao.idlotacao = '.$this->lotacao.')'; 
            }else{ # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
            }
        }

        $select .= ' ORDER BY 2';
        
        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select);
        
        return $retorno;
    }
    
###########################################################
    
}