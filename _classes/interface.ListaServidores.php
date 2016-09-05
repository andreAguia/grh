<?php
/**
 * Exibe uma lista detalhada dos servidores
 * 
 * esta classe foi criada devido a sua grande (re)usabilidade
 * 
 * @author Alat
 */

class listaServidores
{    
    
    # Título
    private $nomeLista = null;  # Nome da lista que aparece no título
    
    # Parâmetros de Pesquisa
    private $matNomeId = null;  # Busca por matricula nome ou id em um só campos
    private $cargo = null;
    private $cargoComissao = null;
    private $perfil = null;
    private $concurso = null;
    private $situacao = null;
    private $situacaoSinal = "=";
    private $lotacao = null;
    
    # Parâmetros da paginação da listagem
    private $paginacao = false;			# Flag que indica se terá ou não paginação na lista
    private $paginacaoItens = 15;		# Quantidade de registros por página. 
    private $paginacaoInicial = 0;		# A paginação inicial
    private $pagina = 1;			# Página atual
    private $quantidadeMaxLinks = 10;           # Quantidade Máximo de links de paginação a ser exibido na página
    
    ###########################################################
                
    /**
     * método construtor
     * inicia um Formulário
     * 
     * @param  $name    = nome da classe e do id para estilo
     */
    
    public function __construct($nome){
        $this->nomeLista = $nome;
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
     * Método show
     * 
     * Exibe a lista
     *
     */	
    public function show()
    {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ='SELECT tbservidor.idFuncional,
                         tbservidor.matricula,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                         tbperfil.nome,
                         tbservidor.dtAdmissao,
                         tbsituacao.situacao,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                         JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                    LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                    LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
        
        # Matrícula, nome ou id
        if(!is_null($this->matNomeId))
            $select .= ' AND ((tbpessoa.nome LIKE "%'.$this->matNomeId.'%")
                   OR (tbservidor.matricula LIKE "%'.$this->matNomeId.'%")
		   OR (tbservidor.idfuncional LIKE "%'.$this->matNomeId.'%"))';        
                
        # situação
        if(!is_null($this->situacao))
            $select .= ' AND (tbsituacao.idsituacao '.$this->situacaoSinal.' "'.$this->situacao.'")';
        
        # perfil
        if(!is_null($this->perfil))
            $select .= ' AND (tbperfil.idperfil = "'.$this->perfil.'")';
        
        # cargo
        if(!is_null($this->cargo))
            $select .= ' AND (tbcargo.idcargo = "'.$this->cargo.'")';
        
        # cargo em comissão
        if(!is_null($this->cargoComissao))
            $select .= ' AND ((SELECT tbtipocomissao.descricao FROM tbcomissao JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao) WHERE dtExo is NULL AND tbcomissao.idServidor = tbservidor.idServidor) = "'.$this->cargoComissao.'")';    
            
        # concurso
        if(!is_null($this->concurso))
            $select .= ' AND (tbservidor.idConcurso = "'.$this->concurso.'")'; 
        
        # lotacao
        if(!is_null($this->lotacao))
            $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';                

        # ordenação
        $select .= ' ORDER BY tbpessoa.nome';
        
        # Pega a quantidade de itens da lista
        $conteudo = $servidor->select($select,true);
        $totalRegistros = count($conteudo);
        
        # Verifica a necessidade de paginação pelo número de registro
        if($this->paginacaoItens >= $totalRegistros){
            $this->paginacao = FALSE;
        }
        
        # Verifica se página Inicial que veio por session deverá ser atualizada para 0
        if($this->paginacaoInicial > $totalRegistros){
            $this->paginacaoInicial = 0;
        }
                
        # Calculos da paginaçao
        $texto = null;
        if($this->paginacao)
        {
            # Calcula o total de páginas
            $totalPaginas = ceil($totalRegistros/$this->paginacaoItens);

            # Calcula o número da página
            $this->pagina = ceil($this->paginacaoInicial/$this->paginacaoItens)+1;

            # Calcula o item inicial e final da página
            $itemFinal = $this->pagina * $this->paginacaoItens;
            $itemInicial = $itemFinal - $this->paginacaoItens+1;

            if ($itemFinal > $totalRegistros)
            $itemFinal = $totalRegistros;

            # Texto do fieldset
            $texto = 'Página: '.$this->pagina.' de '.$totalPaginas;
        
            # Acrescenta a sql
            $select.=' LIMIT '.$this->paginacaoInicial.','.$this->paginacaoItens;

            # Botôes de Navegação das páginas 
            $proximo = $this->paginacaoInicial + $this->paginacaoItens;
            $anterior = $this->paginacaoInicial - $this->paginacaoItens;            
        }
        
        # Botões de paginação
        if($this->paginacao){
            # Começa os botões de navegação
            $div = new Div("paginacao");
            $div->abre();            
            echo'<ul class="pagination text-center" role="navigation" aria-label="Pagination">';

            # Botão Página Anterior
            if($this->pagina == 1){
                echo '<li class="pagination-previous disabled"><span class="show-for-sr">page</span></li>';
            }else{
                echo '<li class="pagination-previous"><a href="?paginacao='.$anterior.'" aria-label="Página anterior"></a></li>';
            }

            # Links para a página
            for($pag = 1;$pag <= $totalPaginas; $pag++){
                if($pag == $this->pagina){
                    echo '<li class="current"><span class="show-for-sr">Página Atual</span> '.$pag.'</li>';
                }else{
                    $link = $this->paginacaoItens * ($pag-1);
                
                    if($totalPaginas > $this->quantidadeMaxLinks){
                        switch ($pag) {
                            case 1:
                            case 2:    
                                echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a></li>';
                                break;
                            case 3:
                                if($this->pagina == 2){
                                    echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a></li>';  
                                }else{
                                    echo '<li>...<li>';
                                }
                                break;
                            case $this->pagina-1:
                            case $this->pagina+1:    
                                echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a><li>';
                                break;
                            case $totalPaginas-2:
                                if($this->pagina == $this->pagina-4){
                                    echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a></li>';  
                                }else{
                                    echo '<li>...<li>';
                                }
                                break;
                            case $totalPaginas-1:
                            case $totalPaginas:
                                echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a></li>';
                                break;
                        }                                
                    }else{
                        echo '<li><a href="?paginacao='.$link.'" aria-label="Pagina '.$pag.'">'.$pag.'</a></li>';
                    }
                }
            }

            # Botão Próxima Página
            if($this->pagina < $totalPaginas){
                echo '<li class="pagination-next"><a href="?paginacao='.$proximo.'" aria-label="Próxima página"><span class="show-for-sr">page</span></a></li>';
            }else{
                echo '<li class="pagination-next disabled"><span class="show-for-sr">page</span></li>';
            }
            echo '</ul>';
            $div->fecha();
        }       

        # Dados da Tabela
        $label = array("IDFuncional","Matrícula","Servidor","Cargo","Lotação","Perfil","Admissão","Situação");
        $width = array(5,5,15,16,15,8,8,5,5);
        $align = array("center","center","left");
        $function = array (null,"dv",null,null,null,null,"date_to_php");
        $classe = array(null,null,null,"pessoal");
        $metodo = array(null,null,null,"get_Cargo");
        
        # Pega a lista com o limit da tabulação
        titulo($this->nomeLista);
        
        $conteudo = $servidor->select($select,true);
        
        if($totalRegistros == 0){
            br();
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
        else
        {
            # Monta a tabela
            $tabela = new Tabela();
            
            $tabela->set_conteudo($conteudo);
            $tabela->set_cabecalho($label,$width,$align);
            #$tabela->set_titulo($this->nomeLista);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_funcao($function);
            $tabela->set_totalRegistro(true);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('servidor.php?fase=editar&id=');
            
            if ($this->paginacao)
                $tabela->set_footTexto($texto.' ('.$itemInicial.' a '.$itemFinal.' de '.$totalRegistros.' Registros)');
            
            if(!is_null($this->matNomeId))
                $tabela->set_textoRessaltado($this->matNomeId);
            
            $tabela->show();
        }
    }
}