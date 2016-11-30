<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = null;

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
    $lotacao = get('lotacao',post('lotacao'));

    ######
    
    $select ='SELECT idPerfil,
                    nome,
                    tipo,
                    progressao,
                    trienio,
                    comissao,
                    gratificacao,
                    ferias,
                    licenca,
                    idPerfil,
                    idPerfil
               FROM tbperfil
           ORDER BY idPerfil';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Perfil');
    #$relatorio->set_subtitulo('Agrupados por Diretoria - Ordenados pelo Nome');
    $relatorio->set_label(array("id","Perfil","Tipo","Progressão","Triênio","Cargo em Comissão","Gratificação","Férias","Licença"));
    $relatorio->set_width(array(5,21,20,9,9,9,9,9,9));
    $relatorio->set_align(array("center"));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}