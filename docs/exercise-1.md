# Exercise 1 — Fundamentos Yii2 (registro de progresso)

Branch: `exercise-1`

## Objetivo
Implementar tela de login + criação de produto usando DDD Lite.

## Decisões iniciais
- Arquitetura: DDD Lite com camadas Domain / Application / Infrastructure.
- Local do Yii2: `src/Infrastructure/Yii`.
- Produto armazena: `id, name, type_id, price, code, created_at`.
- Tipos de produto: tabela `product_type` (criada via migration).
- Campo `code` será calculado no UseCase `CreateProduct`, persistido na entidade Product.

## Checklist
- [x] Criar migrations iniciais (product_type, product)
- [x] Gerar AR via Gii
- [ ] Criar Domain/Repository interface
- [ ] Implementar Repository na Infra (usando AR)
- [ ] Criar UseCase `CreateProduct`
- [ ] Criar Controller `ProductController` e views (login + create)
- [ ] Criar testes unitários para UseCase e Repository

## Motivações:

#### 1. Criar migrations iniciais (product_type, product)

- A migração `m251127_001237_add_product_type_schema` adiciona uma tabela `product_type` com a intenção de: 
  - padronizar os tipos de produtos disponíveis, para não permitir registros da tabela `product` com tipos duplicados. 
  - evitar que nomes similares, por exemplo `Dress` e `dress`, sejam considerados iguais, assim foi adicionada a coluna `normalized_name`, com restrição **'unique'**.

- Sobre a migração da tabela `product`, a motivação foi:
  - "fortalecer" a consistência dos registros, usando as ferramentas já existentes em bancos de dados do paradigma relacional, como: não permitir cadastrar preço como `NULL`; criar uma chave estrangeira `type_id` referenciando `product_type` com restrição `NOT NULL`
  - promover a regra de negócios para o código do produto que siga o algoritmo `type + 000 + N`. Sendo que column `code` representa o código do produto e pode começar com "0" (**zero**). Assim, decidi usar "VARCHAR", ao invés de um tipo numérico, pois o objetivo é não quebrar o código quando esse iniciar com **zero**.

#### 2. Gerar Active Recrods via Gii

- Foram geradas as classes **Active Record** de `Product` e `ProductType` dentro da camada de Infraestrutura.
- Esses Active Records serão usados apenas na Infra para mapeamento e persistência (AR ↔ Entidade do Domain). Eles não devem conter regras de negócio.
- Todas as regras de negócio e invariantes ficam nas entidades de Domain e na lógica de orquestração da camada Application (UseCases). A camada Application usa DTOs na fronteira (entrada/saída) e repositórios do Domain como portas para a Infra.