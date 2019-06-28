<?php
class ListaFerias{
 /**
  * Exibe várias informações em forma de listas sobre as férias dos servidores
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $anoExercicio   integer NULL O Ano de exercícios das férias
  * @var private $lotacao        integer NULL O id da lotação. Quando NULL exibe de todas a universidade
  * @var private $permiteEditar  boolean TRUE Indica se terá botão para acessar informções dos servidores
  */
    
    private $anoExercicio = NULL;
    private $lotacao = NULL;
    private $permiteEditar = TRUE;
    
    ###########################################################
    
    public function __construct($anoExercicio){
                
    /**
     * Inicia a classe atribuindo um valor ao anoExercicio
     * 
     * @param $anoExercicio integer NULL O Ano de exercícios das férias
     */    
    
        $this->anoExercicio = $anoExercicio;
    }
        
    ###########################################################
    
    public function set_lotacao($idLotacao = NULL){
    /**
     * Informa a lotação dos servidores cujas ferias serão exibidas
     * 
     * @param $idLotacao integer NULL o idLotacão da lotação a ser exibida as férias
     * 
     * @note Quando o $idLotacao não é informado será exibido de todas as lotações.
     * 
     * @syntax $ListaFerias->set_lotacao([$idLotacao]);  
     */
    
        # Força a ser nulo mesmo quando for ""
        if(vazio($idLotacao)){
            $idLotacao = NULL;
        }
        
        # Transforma em nulo a máscara *
        if($idLotacao == "*"){
            $idLotacao = NULL;
        }
        
        $this->lotacao = $idLotacao;
    }
    
    ###########################################################
    
    public function showResumoGeral(){
   
    /**
     * Informa os totais de servidores do setor com ou sem férias
     * 
     * @syntax $ListaFerias->showResumoGeral();  
     *
     */	        
        # Servidores desse setor que solicitaram férias
        $servset1 = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $totalServidores1 = count($servset1);
        
        # Servidores desse setor que NÃO solicitaram férias
        $semFerias = array();                           // Array dos servidores sem férias    
        $servset2 = $this->getServidoresSemFerias();    // Os que não pediram férias
        $totalServidores2 = count($servset2);
        $semFerias[] = array("Solicitaram",$totalServidores1);
        $semFerias[] = array("Não Solicitaram",$totalServidores2);
        $totalServidores3 = $totalServidores1 + $totalServidores2;
        
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($semFerias);
        $tabela->set_label(array("Descrição","Nº de Servidores"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center"));
        $tabela->set_titulo("Resumo Geral");
        $tabela->set_rodape("Total de Servidores: ".$totalServidores3);
        $tabela->show();
        
        # Coloca no array para exibição o número de servidores sem ferias
        
    }
    
    ###########################################################
       
    public function showResumoPorDia(){
   
    /**
     * Informa os totais de servidores que solicitaram férias por total de dias solicitados
     * 
     * @syntax $ListaFerias->showResumoPorDia();  
     *
     */	        
        # Pega um array com os totais dos dias de férias dessa lotação nesse anoexercicio
        $diasTotais = $this->getDiasFerias(); 
        
        # Conta o número de dias 
        $totalFerias = count($diasTotais);
       
        $conta = array();   // Array para exibir na tela    
        $tt = 0;            // Totalizador de servidores que pediram férias
        
        # Informa quantos servidores em cada total de dias
        if($totalFerias > 0){
            $conta = $this->getTotalServidorDiasFerias($diasTotais);
            
            # Soma os servidores que periram férias nesse exercício e nessa lotação
            foreach ($conta as $contaSomada) {
                $tt += $contaSomada[0];
            }
        }
        
        # Exibe os servidores desse setor que solicitaram férias
        $servset = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $totalServidores = count($servset);                    // Conta o número de servidores
        
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conta);
        $tabela->set_label(array("Dias","Servidores"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center"));
        $tabela->set_titulo("Servidores Por Dia");
        $tabela->set_rodape("Total de Servidores: ".$totalServidores);
        $tabela->show();        
    }
    
    ###########################################################
   
    /**
     * Método showPorDia
     * 
     * Exibe um resumo geral das férias por lotação
     *
     */	
    public function showPorDia(){
        
        # Pega um array com os totais dos dias de férias dessa lotação nesse anoexercicio
        $diasTotais = $this->getDiasFerias(); 
        
        # Conta o número de dias 
        $totalFerias = count($diasTotais);
       
        $conta = array();   // Array para exibir na tela    
        $tt = 0;            // Totalizador de servidores que pediram férias
        
        # Informa quantos servidores em cada total de dias
        if($totalFerias > 0){
            $conta = $this->getTotalServidorDiasFerias($diasTotais);
            
            # Soma os servidores que periram férias nesse exercício e nessa lotação
            foreach ($conta as $contaSomada) {
                $tt += $contaSomada[0];
            }
        }
        
        # Exibe os servidores desse setor
        $servset1 = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $servset2 = $this->getServidoresSemFerias();            // Os que não pediram férias
        $servset3 = array_merge_recursive($servset2,$servset1); // Junta os dois
        $totalServidores = count($servset3);                    // Conta o número de servidores
                
        # Monta a tabela de Servidores.
        if($totalServidores > 0){
            
            $tabela = new Tabela();
            $tabela->set_titulo("Ano Exercício: ".$this->anoExercicio);
            $tabela->set_label(array("Id","Servidor","Lotação","Perfil","Admissão","Dias","Situação"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_lotacaoSimples","get_perfilSimples"));
            $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
            $tabela->set_align(array("center","left","left"));
            $tabela->set_idCampo('idServidor');
            $tabela->set_formatacaoCondicional(array(
                                               array('coluna' => 5,
                                                     'valor' => 30,
                                                     'operador' => '>',
                                                     'id' => 'problemas'),
                                               array('coluna' => 5,
                                                     'valor' => 30,
                                                     'operador' => '=',
                                                     'id' => 'certo'),
                                               array('coluna' => 5,
                                                     'valor' => 30,
                                                     'operador' => '<',
                                                     'id' => 'faltando')));
            
            if($this->permiteEditar){
                $tabela->set_editar('?fase=editaServidorFerias&id=');
                $tabela->set_nomeColunaEditar("Acessar");
                $tabela->set_editarBotao("ver.png");
            }           
            
            $tabela->set_conteudo($servset3);
            $tabela->show();
        }              
    }
    
    ###########################################################
   
    /**
     * Método getDiasFerias
     * 
     * Informa os totais de dias de férias de uma determinada lotação de uma ano exercício
     *
     */	
    private function getDiasFerias($idLotacao = NULL){
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Pega os dias totais desse exercício/Lotação
        $slctot = "SELECT distinct sum(numDias) as soma
                     FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                LEFT JOIN tbferias USING (idServidor)
                     JOIN tbhistlot USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE anoExercicio = $this->anoExercicio
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";
        
        # Verifica se tem filtro por lotação
        if(!is_null($idLotacao)){ // dá prioridade ao filtro da função
            $slctot .= ' AND (tblotacao.idlotacao = "'.$idLotacao.'")';
        }elseif(!is_null($this->lotacao)){  // senão verifica o da classe
            $slctot .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }
        
        $slctot .= "GROUP BY idServidor
                    ORDER BY soma desc";
        
        $diasTotais = $servidor->select($slctot);
        return $diasTotais;
    }
    
    ###########################################################
   
    /**
     * Método getTotalServidorDiasFerias
     * 
     * Informa array com os totais de servidores pelo total de dias de férias de uma determinada lotação de uma ano exercício
     *
     */	
    private function getTotalServidorDiasFerias($diasTotais){
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        foreach ($diasTotais as $valor) {
            $slctot = "SELECT idServidor,
                              sum(numDias) as soma
                         FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                       LEFT JOIN tbferias USING (idServidor)
                                            JOIN tbhistlot USING (idServidor)
                                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE anoExercicio = $this->anoExercicio 
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                        if(!is_null($this->lotacao)){
                            $slctot .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
                        }

             $slctot .= "
                     GROUP BY idServidor
                     HAVING soma = $valor[0]
                     ORDER BY 1";
            $num = $servidor->count($slctot);
            $conta[] = array($valor[0],$num);            
        }
        
        return $conta;
    }
    
    ###########################################################
   
    /**
     * Método getServidoresComTotalDiasFerias
     * 
     * Informa array com todos os servidores que pediram férias desse setor e o total de dias
     *
     */	
    private function getServidoresComTotalDiasFerias(){
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        $select1 = "(SELECT tbservidor.idFuncional,
                            tbpessoa.nome,
                            tbservidor.idServidor,
                            tbservidor.idServidor,
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma,
                            tbsituacao.situacao
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                     LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";
        
        # Verifica se tem filtro por lotação
        if(!is_null($this->lotacao)){  // senão verifica o da classe
            $select1 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }
        
        $select1 .= "
              AND anoExercicio = $this->anoExercicio
        GROUP BY tbpessoa.nome
         ORDER BY soma,tbpessoa.nome)";
        
        # Pega os dados do banco
        $retorno = $servidor->select($select1,TRUE);
        
        return $retorno;
    }
    
    ###########################################################
   
    /**
     * Método getServidoresSemFerias
     * 
     * Informa array com todos os servidores que não pediram férias desse setor
     *
     */	
    private function getServidoresSemFerias(){
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        $select2 = "SELECT tbservidor.idFuncional,
                           tbpessoa.nome,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbsituacao.situacao
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < $this->anoExercicio
                      ";
                 
        # Verifica se tem filtro por lotação
        if(!is_null($this->lotacao)){  // senão verifica o da classe
            $select2 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }

        $select2 .= "
             AND tbservidor.situacao = 1
             AND tbpessoa.nome NOT IN 
             (SELECT tbpessoa.nome
             FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                JOIN tbferias USING (idservidor)
                                JOIN tbhistlot USING (idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoExercicio = $this->anoExercicio";

        if(!is_null($this->lotacao)){
            $select2 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }

        $select2 .= "
                AND tbservidor.situacao = 1
           ORDER BY tbpessoa.nome asc)
              ORDER BY tbpessoa.nome asc";        
        
        # Pega os dados do banco
        $retorno = $servidor->select($select2,TRUE);
        
        return $retorno;
    }
}