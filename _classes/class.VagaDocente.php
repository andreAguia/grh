<?php
class VagaDocente{
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
    
    function get_dados($idVagaDocente){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbvagadocente
                  WHERE idVagaDocente = $idVagaDocente";
        
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
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_servidorOcupante($idVagaDocente){
        
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $this->get_nome($dado[0]);
            
        }else{
            return $idVagaDocente;
        }
    }

    ###########################################################

    /**
     * Método get_laboratorioOcupante
     * fornece o nome do laboratório de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_laboratorioOcupante($idVagaDocente)
    {
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idLotacao
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $pessoal->get_nomeLotacao2($dado[0]);
            
        }else{
            return $idVagaDocente;
        }
    }

    ###########################################################

    /**
     * Método get_concursoOcupante
     * fornece o nome do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_concursoOcupante($idVagaDocente)
    {
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y"))
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVagaDocente;
        }
    }

    ###########################################################

    /**
     * Método get_areaOcupante
     * fornece area do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_areaOcupante($idVagaDocente)
    {
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT area
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVagaDocente;
        }
    }

    ###########################################################

    /**
     * Método get_obsOcupante
     * fornece a obs da vaga do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_obsOcupante($idVagaDocente)
    {
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT tbconcursovaga.obs
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVagaDocente;
        }
    }

    ###########################################################

    /**
     * Método get_status
     * fornece o status da vaga
     * 
     * @param	string $idVagaDocente O id da vaga do servidor
     */

    function get_status($idVagaDocente){
        
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor
                         FROM tbconcursovaga JOIN tbconcurso USING (idConcurso)
                        WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
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
     * @param	string $idVagaDocente O id da vaga
     */

    function get_idCargoVaga($idVagaDocente)
    {
        if(is_numeric($idVagaDocente)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idCargo
                         FROM tbvagadocente
                        WHERE idVagaDocente = '.$idVagaDocente;
            
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
     * @param	string $idVagaDocente O id da vaga
     */

    function exibeDadosVaga($idVagaDocente){ 
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ="SELECT centro,
                         tbcargo.nome,
                         idVagaDocente
                    FROM tbvagadocente LEFT JOIN tbcargo USING (idCargo)
                   WHERE idVagaDocente = $idVagaDocente";
        
        $conteudo = $servidor->select($select,TRUE);
        
        $label = array("Centro","Cargo","Status");        
        $classe = array(NULL,NULL,"VagaDocente");
        $metodo = array(NULL,NULL,"get_status");

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_titulo("Vaga de Docente");
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
        br();
    }

    ###########################################################

}