package main

import (
	"fmt"
	"math"
	"os"
	"strings"
)

var antennaPairs []AntennaPair
var maxX, maxY int

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		var antennasMapByFrequency = make(map[string][][]bool)

		antennaPairs = make([]AntennaPair, 0)
		lines := strings.Split(string(fileContents), "\n")
		maxX = len(lines[0]) - 1
		maxY = len(lines) - 1
		for lineIndex, line := range lines {
			frequencies := strings.Split(line, "")
			for frequencyIndex, frequency := range frequencies {
				if frequency == "." {
					continue
				}
				_, ok := antennasMapByFrequency[frequency]
				if !ok {
					antennasMapByFrequency[frequency] = make([][]bool, len(lines))
					for y := 0; y < len(lines); y++ {
						antennasMapByFrequency[frequency][y] = make([]bool, len(line))
					}
				}
				//if antennasMapByFrequency[frequency][lineIndex] == nil {
				//	antennasMapByFrequency[frequency][lineIndex] = make([]bool, len(line))
				//}
				antennasMapByFrequency[frequency][lineIndex][frequencyIndex] = true
			}
		}
		for _, thisFrequencyMap := range antennasMapByFrequency {
			for y := 0; y < len(thisFrequencyMap); y++ {
				for x := 0; x < len(thisFrequencyMap[y]); x++ {
					if !thisFrequencyMap[y][x] {
						continue
					}
					for y2 := 0; y2 < len(thisFrequencyMap); y2++ {
					pairSearch:
						for x2 := 0; x2 < len(thisFrequencyMap[y2]); x2++ {
							if y == y2 && x == x2 {
								continue
							}
							for _, pair := range antennaPairs {
								if pair.antenna1.x == x && pair.antenna1.y == y && pair.antenna2.x == x2 && pair.antenna2.y == y2 {
									continue pairSearch
								}
								if pair.antenna2.x == x && pair.antenna2.y == y && pair.antenna1.x == x2 && pair.antenna1.y == y2 {
									continue pairSearch
								}
							}
							if thisFrequencyMap[y2][x2] {
								antennaPairs = append(antennaPairs, AntennaPair{coordinates{x, y}, coordinates{x2, y2}})
							}
						}
					}
				}
			}
		}
		var antiMap = make([][]bool, len(lines))
		for y := 0; y < len(lines); y++ {
			antiMap[y] = make([]bool, len(lines[y]))
		}
		for _, pair := range antennaPairs {
			antis := antiForPair(pair)
			for _, anti := range antis {
				addAntennaIfWithinBounds(anti, &antiMap)
			}

		}
		var total = 0
		for y := 0; y <= maxY; y++ {
			for x := 0; x <= maxX; x++ {
				if antiMap[y][x] {
					total++
				}
			}
		}

		//debug
		for y := 0; y <= maxY; y++ {
			print("\n")
			for x := 0; x <= maxX; x++ {
				if antiMap[y][x] {
					print("#")
					continue
				}
				var freqFound bool
				for key, thisFrequencyMap := range antennasMapByFrequency {
					if thisFrequencyMap[y][x] {
						print(key)
						freqFound = true
						break
					}
				}
				if !freqFound {
					print(".")
				}
			}
		}
		print("\n")
		println(total)

	}

}
func addAntennaIfWithinBounds(antenna coordinates, antiMap *[][]bool) {
	if antenna.y > maxY || antenna.y < 0 {
		return
	}
	if antenna.x > maxX || antenna.x < 0 {
		return
	}

	(*antiMap)[antenna.y][antenna.x] = true
}
func antiForPair(pair AntennaPair) []coordinates {
	//y=a+bx
	b := float64(pair.antenna2.y-pair.antenna1.y) / float64(pair.antenna2.x-pair.antenna1.x)
	a := float64(pair.antenna1.y) - b*float64(pair.antenna1.x)
	result := make([]coordinates, 0)
	for x := 0; x <= maxX; x++ {
		//Not rounding the y will produce a wrong answer due to float math
		//Not sure how this rounding point is supposed to be picked, but 10 digits seemed to work
		var y = math.Round((a+b*float64(x))*10000000000) / 10000000000
		if y != float64(int(y)) {
			continue
		}
		if int(y) <= maxY && int(y) >= 0 {
			result = append(result, coordinates{x, int(y)})
		}
	}
	return result
}

type coordinates struct {
	x, y int
}
type AntennaPair struct {
	antenna1, antenna2 coordinates
}
