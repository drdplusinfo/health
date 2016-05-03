### Wounds
 
 - aggregates every single not-yet healed wound
 - knows number of serious injuries (six means death)
 - knows malus caused by wounds and roll on will
 - knows if person is still alive
 - note: sum of wounds (its points) is kept by `Grid of wounds`
 
#### Grid of wounds

 - its three-rows size is determined by `wounds limit` (filled rows have effect on `malus`)
 - knows sum of `Wound`s (their `points of wound` respectively)
 - knows remaining health
 - knows filled "rows" count (useful for `roll on will` to get `malus caused by wounds`)
 - knows `Treatment boundary`

##### Wound

 - has `Origin`
 - has size (expressed by number of `point of wound`)
 - has severity
    - ordinary
    - serious, which has detail about its origin, see `Wounds origin`,
    also serious wound is automatically considered as `old injury` (see `Treatment boundary`)
    
##### Wounds origin

###### Mechanical wounds

 - stab
 - cut
 - crush

###### Psychical wounds

 - Ψ [ˈpsiː]
 
###### Elemental wounds
 
 - ± fire
 - ± air
 - ± water
 - ± earth
 
##### Treatment boundary

 - splits wounds in `grid of wounds` into two parts
    - "old" injuries, which are those not healed by last treatment (native regeneration per day, or by a healer) and all serious injuries
    - new injuries

### Affliction (handicap caused by body or soul damage)

Knows its

 - domain
 - virulence
 - source
 - property
 - dangerousness
 - size
 - elemental pertinence
 - effect
 - outbreak period
 
### Healing

#### Mitigation of wounds

##### Treatment of ordinary wounds

##### Treatment of serious wounds

##### Native healing of wounds

### Healing of affliction

#### Treatment of affliction

#### Native cure of affliction
