<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $relatorioLotacao = post('lotacao','CBB');
    
    ######
    
    $select ='SELECT tbpessoa.nome,
                     tbdocumentacao.cpf,                    
                     "______________________________________________"
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
                                     JOIN tbdocumentacao using (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.tipo = "Professor"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    # lotacao
    if(!is_null($relatorioLotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($relatorioLotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$relatorioLotacao.'")';
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$relatorioLotacao.'")';
        }
    }
    
    $select .= ' ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_tituloLinha2($relatorioLotacao);
    $relatorio->set_subtitulo('Ordenado pelo Nome');
    $relatorio->set_label(array('Nome','CPF','Assinatura'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("left","left","left"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    
    #$relatorio->set_classe(array(NULL,NULL,"pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,"get_CargoSimples"));
    
    $relatorio->set_conteudo($result);
    
    # Dados da combo lotacao
    $lotacao = $servidor->select('SELECT distinct DIR, DIR
                                   FROM tblotacao
                                  WHERE ativo
                               ORDER BY 2');
    
    #$relatorio->set_bordaInterna(TRUE);
    #$relatorio->set_cabecalho(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'lotacao',
                         'label' => 'Lotação',
                         'tipo' => 'combo',
                         'array' => $lotacao,
                         'col' => 3,
                         'size' => 10,
                         'padrao' => $relatorioLotacao,
                         'title' => 'Filtra por Lotação.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}