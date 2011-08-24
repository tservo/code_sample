require 'power_set'

describe PowerSet do
    let (:empty_set) { Set.new }
    let (:set_a) { Set.new("a") }
    let (:set_b) { Set.new("b") }
    let (:set_c) { Set.new("c") }
    
    it "P({}) => { {} }" do
        p_set = PowerSet.new(empty_set)
        p_set.should == [ empty_set ]
    end
    
    it "P({a}) => { {}, {a} }" do
        p_set = PowerSet.new(set_a)
        p_set.should == [ empty_set, set_a ]
    end
    
    it "add {a} to P({}) => { {}, {a} }" do
        p_set = PowerSet.new(empty_set)
        p_set.add(set_a)
        p_set.should == [ empty_set, set_a ]
    end
    
    it "add {} to P({a}) => { {}, {a} }" do
        p_set = PowerSet.new(set_a)
        p_set.add(empty_set)
        p_set.should == [ empty_set, set_a ]
    end
        
    it "P({a,b}) => { {}, {a}, {b}, {a,b} }" do
        p_set = PowerSet.new(set_a + set_b)
        p_set.should == [ empty_set, set_a, set_b, set_a + set_b ]
    end
    
    it "add {b} to P({a}) => { {}, {a}, {b}, {a,b} }" do
        p_set = PowerSet.new(set_a)
        p_set.add(set_b)
        p_set.should == [ empty_set, set_a, set_b, set_a + set_b ]
    end
    
    it "P({a,b,c}) should have 8 elements" do
        p_set = PowerSet.new(set_a+set_b+set_c)
        p_set.count.should == 8
    end
    
    it "P({a,b}) == add {b} to P({a})" do
        p_set1 = PowerSet.new(set_a+set_b)
        p_set2 = PowerSet.new(set_a).add(set_b)
        p_set1.should == p_set2
    end
    
    it "subtract {c} from P({a,b,c}) == P({a,b})" do
        p_set1 = PowerSet.new(set_a+set_b+set_c)
        p_set1.subtract(set_c)
        p_set2 = PowerSet.new(set_a+set_b)
        p_set1.should == p_set2
    end
end