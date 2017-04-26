<?php
/**
 * Exibe uma lista detalhada dos servidores
 * 
 * esta classe foi criada devido a sua grande (re)usabilidade
 * 
 * @author Alat
 */

class listaFerias
{    
    # Parâmetros de Pesquisa    
    private $anoExercicio = NULL;
    private $lotacao = NULL;    
    
    # Parâmetro de edição
    private $permiteEditar = TRUE;          # Indica se terá botão para acessar informções dos servidores
    
    # Outros
    private $totReg = 0;     # total de registros encontrados
    private $time_start = 0; # Contador de segundos gastos na pesquisa
    
    # Parâmetros do relatório
    private $select = NULL;     // Guarda o select para ser recuperado pela rotina de relatório
    private $titulo = NULL;     // guarda o título do relatório que é montado a partir da pesquisa
    private $subTitulo = NULL;  // guarda o subTítulo do relatório que é montado a partir da pesquisa
    
    ###########################################################
                
    /**
     * método construtor
     * inicia um Formulário
     * 
     * @param  $name    = nome da classe e do id para estilo
     */
    
    public function __construct($ano){
        $this->anoExercicio = $ano;
    }
    
    ###########################################################

    /**
    * Métodos get e set construídos de forma automática pelo 
    * metodo mágico __call.
    * Esse método cria um set e um get para todas as propriedades da classe.
    * Um método existente tem prioridade sobre os métodos criados pelo __call.
    * 
    * O formato dos métodos devem ser:
    * 	set_propriedade
    * 	get_propriedade
    * 
    * @param 	$metodo		O nome do metodo
    * @param 	$parametros	Os parâmetros inseridos  
    */
    
    public function __call ($metodo, $parametros)
    {
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set')
        {
            $var = substr($metodo, 4);
            $this->$var = $parametros[0];
        }

        # Se for Get, retorna o valor da propriedade
        if (substr($metodo, 0, 3) == 'get')
        {
            $var = substr($metodo, 4);
            return $this->$var;
        }
    }
    
    ###########################################################
   
    /**
     * Método showResumoGeral
     * 
     * Exibe um resumo geral das férias por lotação
     *
     */	
    public function showResumoGeral(){
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Informa as lotações ativas
        $selectLotacao = 'SELECT idlotacao,
                                 concat(IFNULL(UADM,"")," - ",IFNULL(DIR,"")," - ",IFNULL(GER,"")," - ",IFNULL(nome,"")) lotacao
                          FROM tblotacao       
                         WHERE ativo
                        ORDER BY lotacao'; 
        $conteudo = $servidor->select($selectLotacao,TRUE);
        
        # Cria o array para a tabela
        $listaTabela = array();
        
        # Define as variáveis
        $diasFeriasMulti = $this->getDiasFerias();  // array total de dias multi
        $diasTotais = array();                      // array total de dias simples
        $diasTotaisPorLotacaoMulti = array();       // array total de dias por lotacão mult 
        $diasTotaisPorLotacao = array();            // array total de dias por lotacão 
        $tt1 = count($diasFeriasMulti);              // contador de dias
       
        # Transforma $diasTotais (array multi) em array simples
        for ($i = 0; $i < $tt1; $i++){
            $diasTotais[$i] = $diasFeriasMulti[$i][0];
        }
        
        # Adiciona no fim de $dias Totais a coluna para quem não solicitou férias
        array_push($diasTotais, "Não Solicitou");
        
        # Percorre as lotações e preenche a tabela
        foreach ($conteudo as $listaLotacao) {
            # Acrescenta o id e nome da lotação
            $lista = array($listaLotacao[0],$listaLotacao[1]);
                       
            # Pega os dias desse lotação
            $diasTotaisPorLotacaoMulti = $this->getDiasFerias($listaLotacao[0]);
            $tt2 = count($diasTotaisPorLotacaoMulti); 
            
            # Transforma $diasTotaisPorLotacaoMulti (array multi) em array simples
            for ($i = 0; $i < $tt2; $i++){
                $diasTotaisPorLotacao[$i] = $diasTotaisPorLotacaoMulti[$i][0];
            }
            print_r($diasTotaisPorLotacao);
            echo "---";
            br();
            # Acrescenta os totais na coluna dos dias
            foreach ($diasTotais as $dias) {
                $lista[] = "-";
            }
            
            $listaTabela[] = $lista;
        }
        
        # Colocando elementos no início do array
        array_unshift($diasTotais, "ID","Lotação");
        
                
        
        # Monta a tabela de Resumo.
        $tabela = new Tabela();
        
        $tabela->set_conteudo($listaTabela);
        $tabela->set_label($diasTotais);
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center","left"));
        $tabela->set_titulo("Solicitação de Férias por Lotação");
        $tabela->show();
    }
    
    ###########################################################
   
    /**
     * Método showResumo
     * 
     * Exibe a Tabela
     *
     */	
    public function showResumo($resumido = TRUE)
    {
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
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
        $servset3 = array_merge_recursive($servset1,$servset2); // Funta os dois
        $totalServidores = count($servset3);                    // Conta o número de servidores
        
        # Coloca no array para exibição o número de servidores sem ferias
        $conta[] = array(count($servset2),0);
        
        # Monta a tabela de Resumo.
        $tabela = new Tabela();
        
        $tabela->set_conteudo($conta);
        $tabela->set_label(array("Nº de Servidores","Total de Dias"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center"));
        $tabela->set_titulo("Resumo");
        if($resumido){
            $tabela->show();
            titulo("Total: ".$totalServidores);
        }
        
        # Monta a tabela de Servidores.
        if($totalServidores > 0){
            # Monta a tabela
            $tabela = new Tabela();

            $tabela->set_conteudo($servset3);
            $tabela->set_label(array("Id","Servidor","Cargo","Admissão","Dias"));
            $tabela->set_classe(array(NULL,NULL,"pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_cargo"));
            $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php"));
            $tabela->set_align(array("center","left","left"));
            $tabela->set_titulo("Resumo por Servidor");
            $tabela->set_idCampo('idServidor');
            if($this->permiteEditar){
                $tabela->set_editar('?fase=editaServidorFerias&id=');
            }
            if(!$resumido){
                $tabela->show();
            }
        }              
    }
           
    ###########################################################
    
    /**
     * Método showDetalhe
     * 
     * Exibe a Tabela
     *
     */	
    public function showDetalhe()
    {
        # Pega o time inicial
        $this->time_start = microtime(TRUE);
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ='SELECT tbpessoa.nome,
                         tbferias.numDias,
                         tbferias.dtInicial,
                         tbferias.periodo,
                         tbferias.status,
                         tbperfil.nome,                         
                         idFerias
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
			            LEFT JOIN tbferias USING (idservidor) 
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbservidor.situacao = 1
                  AND anoExercicio = '.$this->anoExercicio;
        
        # lotacao
        if(!is_null($this->lotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';                
            $this->subTitulo .= "lotação: ".$servidor->get_nomeLotacao($this->lotacao)." - ".$servidor->get_nomeCompletoLotacao($this->lotacao)."<br/>";
        }
        
        # ordenação
        $select .= ' ORDER BY tbpessoa.nome,tbferias.periodo';
        
        # Pega a quantidade de itens da lista
        $conteudo = $servidor->select($select,TRUE);
        $totalRegistros = count($conteudo);
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Dados da Tabela
        $label = array("Nome","Dias","Data","Período","Status","Perfil");
        #$width = array(5,5,15,16,15,8,8,5,5);
        $align = array("left","center","center","center","center","center");
        $function = array (NULL,NULL,"date_to_php");
                        
        # Executa o select juntando o selct e o select de paginacao
        $conteudo = $servidor->select($select,TRUE);
        
        if($totalRegistros == 0){
            #br();
            #$callout = new Callout();
            #$callout->abre();
            #    p('Não há solicitações de férias !!','center');
            #$callout->fecha();
        }else{
            # Monta a tabela
            $tabela = new Tabela();
            
            $tabela->set_conteudo($conteudo);
            $tabela->set_label($label);
            #$tabela->set_width($width);
            $tabela->set_align($align);
            #$tabela->set_titulo($this->nomeLista);
            #$tabela->set_classe($classe);
            #$tabela->set_metodo($metodo);
            $tabela->set_funcao($function);
            $tabela->set_totalRegistro(TRUE);
            $tabela->set_idCampo('idFerias');
            $tabela->set_titulo("Férias Detalhadas");
            if($this->permiteEditar){
                $tabela->set_editar('?fase=editaFerias&id=');
            }
            
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            
            # Calcula e exibe o tempo
            $time = $time_end - $this->time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
        }
    }
    
    ###########################################################
   
    /**
     * Método relatorio
     * 
     * Exibe a lista
     *
     */	
    public function showRelatorio()
    {
        # Executa rotina interna
        $this->prepara();
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Pega a quantidade de itens da lista
        $conteudo = $servidor->select($this->select,TRUE);
        $totalRegistros = count($conteudo);
        
        # Dados da Tabela
        $label = array("IDFuncional","Matrícula","Servidor","Lotação","Perfil","Exercicio","Status","dt","dias","p");
        #$width = array(5,5,15,16,15,8,8,5,5);
        $align = array("center","center","left","left","left");
        $function = array (NULL,"dv",NULL,NULL,NULL,NULL,NULL,"date_to_php");
        $classe = array(NULL,NULL,NULL,"pessoal");
        $metodo = array(NULL,NULL,NULL,"get_Cargo");
                
        # Relatório
        $relatorio = new Relatorio();
        $relatorio->set_titulo("Relatório");
        if(!is_null($this->subTitulo)){
            $relatorio->set_subtitulo($this->subTitulo);
        }

        $relatorio->set_label($label);
        #$relatorio->set_width($width);
        $relatorio->set_align($align);
        $relatorio->set_funcao($function);
        $relatorio->set_classe($classe);
        $relatorio->set_metodo($metodo);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_conteudo($conteudo);    
        $relatorio->show();
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
                      AND situacao = 1 
                      AND tbferias.status <> 'cancelada'
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
                          AND situacao = 1 
                          AND tbferias.status <> 'cancelada'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                        if(!is_null($this->lotacao)){
                            $slctot .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
                        }

             $slctot .= "
                     GROUP BY idServidor
                     HAVING soma = $valor[0]
                     ORDER BY 1";
            $num = $servidor->count($slctot);
            $conta[] = array($num,$valor[0]);            
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
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                    LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";
        
        # Verifica se tem filtro por lotação
        if(!is_null($this->lotacao)){  // senão verifica o da classe
            $select1 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }
        
        $select1 .= "
              AND tbferias.status <> 'cancelada'
              AND anoExercicio = $this->anoExercicio
              AND situacao = 1 
         GROUP BY tbpessoa.nome
         ORDER BY 5 desc,tbpessoa.nome)";
        
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
                           tbservidor.dtAdmissao,
                           '-'
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      ";
                 
        # Verifica se tem filtro por lotação
        if(!is_null($this->lotacao)){  // senão verifica o da classe
            $select2 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }

        $select2 .= "
             AND situacao = 1
             AND tbpessoa.nome NOT IN 
             (SELECT tbpessoa.nome
             FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                JOIN tbferias USING (idservidor)
                                JOIN tbhistlot USING (idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoExercicio = $this->anoExercicio
                  AND tbferias.status <> 'cancelada'";

        if(!is_null($this->lotacao)){
            $select2 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }

        $select2 .= "
                AND situacao = 1
           ORDER BY tbpessoa.nome asc)
              ORDER BY tbpessoa.nome asc";        
        
        # Pega os dados do banco
        $retorno = $servidor->select($select2,TRUE);
        
        return $retorno;
    }
}