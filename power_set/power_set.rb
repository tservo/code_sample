require 'set'

class PowerSet

    attr_reader :elements
    
    def initialize(set = nil)
        set = Set.new if set.nil?

        @elements = power_set(set)

    end
    
    def add(set)
        return self unless @elements.index(set).nil? # if it's in the powerset, we're done
        @elements = power_set(@elements.last + set) # the last element should be the set the powerset represents
        self
    end

    def subtract(set)
        return self if (@elements.last - set) == @elements.last # if there's no intersection of the parentt set, we're done
        @elements = power_set(@elements.last - set)
        self
    end
    
    
    def ==(pset)
        klass = pset.class
        
        return (elements == pset.elements) if klass == PowerSet
        
        return (@elements == pset) if klass == Array
        
        false
    end
    
    def count
        @elements.count
    end
    
    private
    
    def power_set(set)
        els = []
        (0..set.count).each do |n|
            els += subsets(set, n)
        end
        els
    end
    
    
    def subsets(set, cardinality)
        return [Set.new] if cardinality == 0
        return [set] if cardinality == set.count
        
        arr = []
        skip_els = Set.new
        set.each do |el|
            skip_els += el
            subsets(set - skip_els, cardinality - 1).each do |sl|
                arr << sl + el
            end
        end
        arr
    end
    
end