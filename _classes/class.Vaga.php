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
            
            # Pega os dados
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor,
                              idConcurso,
                              idLotacao,
                              area,
                              tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dados = $pessoal->select($select,FALSE);
            
            $idServidor = $dados["idServidor"];
            return $this->get_nome($idServidor);
            
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

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numVagasCargoDiretoria($idCargo,$dir){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idVaga
                     FROM tbvaga
                    WHERE idCargo = '.$idCargo.'
                      AND centro = "'.$dir.'"';

        $dado = $pessoal->count($select);
        return $dado;
    }
    
    ###########################################################

    /**
     * Método exibeTotalVagas
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function exibeTotalVagas(){
        
        # Conecta o banco
        $pessoal = new Pessoal();

        # Pega as diretorias    
        $diretorias = array("CCT","CCTA","CCH","CBB");
        
        # Pega os Cargos
        $cargos = array(128,129);
        $numeroCargos = count($cargos);
            
        # Cria um array onde terá os resultados
        $resultado = array();

        # Cria e preenche o array do total da coluna
        $totalColuna = array();
        $totalColuna = array_fill(0, $numeroCargos+2, 0);
            
        # Cria e preenche o array do label
        $label = array("Centro");
        foreach($cargos as $cc){
            $label[] = $pessoal->get_nomeCargo($cc);
        }
        $label[] = "Total";
            
        # Zera o contador de linha
        $linha = 0;

        # Percorre as diretorias
        foreach($diretorias as $dd){
            $resultado[$linha][0] = $dd;     // Sigoa da Diretoria 
            $coluna = 1;                        // Inicia a coluna
            $totalLinha = 0;                    // Zera totalizador de cada linha
                
            # Percorre as colunas / Cargos
            foreach($cargos as $cc){
                $quantidade = $this->get_numVagasCargoDiretoria($cc, $dd);    // Pega a quantidade de servidores
                $resultado[$linha][$coluna] = $quantidade;                                                  // Joga para o array de exibição
                $totalLinha = $totalLinha + $quantidade;                                                    // Soma o total da linha a quantidade da coluna
                $totalColuna[$coluna] += $quantidade;                                                       // Soma o total da coluna a quantidade da linha
                $coluna++;                                                                                  // Incrementa a coluna
            }
            # Faz a última coluna com o total da linha
            $resultado[$linha][$coluna] = $totalLinha;
            $totalColuna[$coluna] += $totalLinha;
            $linha++;
        }
        # Faz a última lina com os totais das colunas
        $resultado[$linha][0] = "Total";
        $coluna = 1;
        foreach($cargos as $cc){
            $resultado[$linha][$coluna] = $totalColuna[$coluna];
            $coluna++;
        }
        $resultado[$linha][$coluna] = $totalColuna[$coluna];

        $tabela = new Tabela();
        $tabela->set_titulo("Vagas por Centro / Cargo");
        $tabela->set_conteudo($resultado);
        $tabela->set_label($label);
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("left","center"));       
        $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'estatisticaTotal')));
        $tabela->show();
        
    }
    
    ###########################################################


}