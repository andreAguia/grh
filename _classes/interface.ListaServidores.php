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
    
    private $nomeLista = null;  # Nome da lista que aparece no fieldset
    
    private $matNomeId = null;  # Busca por matricula nome ou id em um só campos
    
    private $cargo = null;
    private $cargoComissao = null;
    private $perfil = null;
    private $concurso = null;
    private $situacao = null;
    private $lotacao = null;
    
    private $fieldset = true;
    private $titulo = false;
    
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
        #if (substr($metodo, 0, 3) == 'get')
        #{
        #    $var = substr($metodo, 4);
        #    return $this->$var;
        #}
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

        $select ='SELECT tbfuncionario.matricula,
                         tbfuncionario.idFuncional,
                         tbpessoa.nome,
                         CONCAT(COALESCE(tbcargo.nome,"")," ",COALESCE(
                         (SELECT tbtipocomissao.descricao FROM tbcomissao JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao) WHERE dtExo is NULL AND tbcomissao.matricula = tbfuncionario.matricula),"")),
                         concat(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao,
                         tbperfil.nome,
                         tbfuncionario.dtAdmissao,
                         tbsituacao.Sit
                    FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                            JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbsituacao ON (tbfuncionario.sit = tbsituacao.idSit)
                                    LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
                                    LEFT JOIN tbcargo ON (tbfuncionario.idCargo = tbcargo.idCargo)
                WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)';
        
        # Matrícula, nome ou id
        if(!is_null($this->matNomeId))
            $select .= ' AND ((tbpessoa.nome LIKE "%'.$this->matNomeId.'%")
                   OR (tbfuncionario.matricula LIKE "%'.$this->matNomeId.'%")
		   OR (tbfuncionario.idfuncional LIKE "%'.$this->matNomeId.'%"))';        
                
        # situação
        if(!is_null($this->situacao))
            $select .= ' AND (tbsituacao.idSit = "'.$this->situacao.'")';
        
        # perfil
        if(!is_null($this->perfil))
            $select .= ' AND (tbperfil.idperfil = "'.$this->perfil.'")';
        
        # cargo
        if(!is_null($this->cargo))
            $select .= ' AND (tbcargo.idcargo = "'.$this->cargo.'")';
        
        # cargo em comissão
        if(!is_null($this->cargoComissao))
            $select .= ' AND ((SELECT tbtipocomissao.descricao FROM tbcomissao JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao) WHERE dtExo is NULL AND tbcomissao.matricula = tbfuncionario.matricula) = "'.$this->cargoComissao.'")';    
            
        # concurso
        if(!is_null($this->concurso))
            $select .= ' AND (tbfuncionario.idConcurso = "'.$this->concurso.'")'; 
        
        # lotacao
        if(!is_null($this->lotacao))
            $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';                

        # ordenação
        $select .= ' ORDER BY tbpessoa.nome';
        
        #echo $select;
        $conteudo = $servidor->select($select,true);

        $label = array("Matrícula","IDFuncional","Servidor","Cargo","Lotação","Perfil","Admissão","Situação");
        $width = array(5,5,15,16,15,8,8,5,5);
        $align = array("center","center","left");
        $function = array ("dv",null,null,null,null,null,"date_to_php");
        
        titulo($this->nomeLista);
        
        if(count($conteudo) == 0){
            br();
            $p = new Alert('Nenhum item encontrado !!','center');
            $p->show();
        }
        else
        {
            # Monta a tabela
            $tabela = new Tabela();
            
            $tabela->set_conteudo($conteudo);
            $tabela->set_cabecalho($label,$width,$align);
            #$tabela->set_titulo($this->nomeLista);
            $tabela->set_funcao($function);
            $tabela->set_totalRegistro(true);
            $tabela->set_idCampo('matricula');
            $tabela->set_editar('servidor.php?fase=editar&id=');
            
            if(!is_null($this->matNomeId))
                $tabela->set_textoRessaltado($this->matNomeId);
            
            $tabela->show();
        }
    }
}