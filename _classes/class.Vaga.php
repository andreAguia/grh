<?php
class Vaga{
 /**
  * Abriga as várias rotina do Controle de Vagas de Docentes
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
    
    function get_dados($idVaga){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbvaga
                  WHERE idVaga = $idVaga";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################

    /**
     * Método get_nome
     * fornece o nome do servidor e outros dados de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_nome($idServidor)
    {
        if(is_numeric($idServidor)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            # Pega os dados
            $nome = $pessoal->get_nome($idServidor);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
            $idSiituacao = $pessoal->get_idSituacao($idServidor);
            $dtSaida = $pessoal->get_dtSaida($idServidor);
            
            if($idSiituacao == 1){
                $css = 'vagasAtivo';
            }else{
                $css = 'vagasInativo';
            }
            
            p($nome,$css);
            p($dtAdmissao."  -  ".$dtSaida,$css);
            
        }else{
            return $idServidor;
        }
    }

    ###########################################################

    /**
     * Método get_servidorOcupante
     * fornece o nome do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_servidorOcupante($idVaga){
        
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $this->get_nome($dado[0]);
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_laboratorioOcupante
     * fornece o nome do laboratório de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_laboratorioOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idLotacao
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $pessoal->get_nomeLotacao2($dado[0]);
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_concursoOcupante
     * fornece o nome do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_concursoOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y"))
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_areaOcupante
     * fornece area do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_areaOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT area
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_obsOcupante
     * fornece a obs da vaga do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_obsOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_status
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_status($idVaga){
        
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            
            if(!vazio($dado[0])){
                # Pega a situação
                $idSituacao = $pessoal->get_idSituacao($dado[0]);

                if($idSituacao == 1){
                    return 'Ocupado';
                }else{
                    return 'em Aberto';
                }
            }else{
                return 'em Aberto';
            }
            
        }else{
            return 'em Aberto';
        }
    }

    ###########################################################

    /**
     * Método get_idCargoVaga
     * fornece o idCargo de uma vaga
     * 
     * @param	string $idVaga O id da vaga
     */

    function get_idCargoVaga($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idCargo
                         FROM tbvaga
                        WHERE idVaga = '.$idVaga;
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return "---";
        }
    }

    ###########################################################

    /**
     * Método exibeDadosVaga
     * fornece os dados de uma vaga em forma de tabela
     * 
     * @param	string $idVaga O id da vaga
     */

    function exibeDadosVaga($idVaga){ 
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ="SELECT centro,
                         tbcargo.nome,
                         idVaga
                    FROM tbvaga LEFT JOIN tbcargo USING (idCargo)
                   WHERE idVaga = $idVaga";
        
        $conteudo = $servidor->select($select,TRUE);
        
        $label = array("Centro","Cargo","Status");        
        $classe = array(NULL,NULL,"Vaga");
        $metodo = array(NULL,NULL,"get_status");

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_titulo("Vaga");
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional(array( array('coluna' => 2,
                                                    'valor' => 'em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),
                                              array('coluna' => 2,
                                                    'valor' => 'Ocupado',
                                                    'operador' => '=',
                                                    'id' => 'alerta')
                                                    ));
        
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);
        
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid(); 
    }

    ###########################################################

    /**
     * Método get_numConcursoVaga
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numConcursoVaga($idVaga){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc';

        $dado = $pessoal->count($select);
        return $dado;
    }

    ###########################################################

}