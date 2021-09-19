replace into BOM (Parent, Component, Quantity, ParentVersion, ComponentVersion)
select distinct Parent, 'LBL0002', 1, ParentVersion, '-any-'
from BOM
where (
    Parent like 'GUM35%'
    or Parent like 'GUM37%'
    or Parent like 'GUM270B%'
    or Parent like 'PKG30%'
    or Parent like 'PKG10%'
)
and ParentVersion != '';