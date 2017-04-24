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
     * Método showResumo
     * 
     * Exibe a Tabela
     *
     */	
    public function showResumo($resumido = TRUE)
    {
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
        
        # lotacao
        if(!is_null($this->lotacao)){
            $slctot .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
        }
        
        $slctot .= "GROUP BY idServidor
                    ORDER BY soma desc";
        
        ###################################################
        
        # Pega os dados do banco
        $diasTotais = $servidor->select($slctot,TRUE);
        $totalFerias = count($diasTotais);
        $conta = array();
        $tt = 0;        // Totalizador de servidores que pediram férias
        
        # Preenche os outros dados
        if($totalFerias > 0){
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
                $tt += $num;
                $conta[] = array($num,$valor[0]);            
            }
        } 
        
        ###################################################
        
        # Exibe os servidores desse setor
        # Os que Pediram férias
        $select1 = "(SELECT tbservidor.idFuncional,
                            tbpessoa.nome,
                            tbservidor.idServidor,
                            sum(numDias) as soma
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                    LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";
        
                        if(!is_null($this->lotacao)){
                            $select1 .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
                        }
        
                 $select1 .= "
                       AND tbferias.status <> 'cancelada'
                       AND anoExercicio = $this->anoExercicio
                       AND situacao = 1 
                  GROUP BY tbpessoa.nome
                  ORDER BY 4 desc,tbpessoa.nome)";
        
        # Pega os dados do banco
        $servset1 = $servidor->select($select1,TRUE);
        
        # Os que não pediram
        $select2 = "SELECT tbservidor.idFuncional,
                           tbpessoa.nome,
                           tbservidor.idServidor,
                           '-'
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      ";
                 
                    if(!is_null($this->lotacao)){
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
        $servset2 = $servidor->select($select2,TRUE);
        $servset3 = array_merge_recursive($servset1,$servset2);
        
        $totalServidores = count($servset3);
        
        # Monta a tabela de Resumo. Está aqui pois preciso pegar o total de servidores
        $tabela = new Tabela();
        
        # Calcula o nº de sevidores sem solicitação de férias nesse exercício
        $totalServidores = $servidor->get_numServidoresAtivos($this->lotacao);
        $conta[] = array($totalServidores - $tt,0);
        
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
            $tabela->set_label(array("Id","Servidor","Cargo","Dias"));
            $tabela->set_classe(array(NULL,NULL,"pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_cargo"));
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
        $conteudo = $servidor->select($select,true);
        $totalRegistros = count($conteudo);
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        # Dados da Tabela
        $label = array("Nome","Dias","Data","Período","Status","Perfil");
        #$width = array(5,5,15,16,15,8,8,5,5);
        $align = array("left","center","center","center","center","center");
        $function = array (null,null,"date_to_php");
                        
        # Executa o select juntando o selct e o select de paginacao
        $conteudo = $servidor->select($select,true);
        
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
            $tabela->set_totalRegistro(true);
            $tabela->set_idCampo('idFerias');
            $tabela->set_titulo("Férias Detalhadas");
            if($this->permiteEditar){
                $tabela->set_editar('?fase=editaFerias&id=');
            }
            
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(true);
            
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
        $conteudo = $servidor->select($this->select,true);
        $totalRegistros = count($conteudo);
        
        # Dados da Tabela
        $label = array("IDFuncional","Matrícula","Servidor","Lotação","Perfil","Exercicio","Status","dt","dias","p");
        #$width = array(5,5,15,16,15,8,8,5,5);
        $align = array("center","center","left","left","left");
        $function = array (null,"dv",null,null,null,null,null,"date_to_php");
        $classe = array(null,null,null,"pessoal");
        $metodo = array(null,null,null,"get_Cargo");
                
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
}